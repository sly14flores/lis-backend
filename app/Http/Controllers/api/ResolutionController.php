<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

use App\Customs\Messages;
use App\Models\Resolution;
use App\Models\CommunicationStatus;

use App\Http\Resources\Resolution\ResolutionResource;
use App\Http\Resources\Resolution\ResolutionListResourceCollection;

class ResolutionController extends Controller
{

    use Messages;

    private $http_code_ok;
    private $http_code_error;    

	public function __construct()
	{
		$this->middleware(['auth:api']);
		// $this->authorizeResource(Group::class, Group::class);
		
        $this->http_code_ok = 200;
        $this->http_code_error = 500;

	}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $filters = $request->all();
        $date_passed = (is_null($filters['date_passed']))?null:$filters['date_passed'];

        $wheres = [];

        if ($date_passed!=null) {
            $wheres[] = ['date_passed', $date_passed];
        }

        $resolutions = Resolution::where($wheres)->paginate(10);

        $data = new ResolutionListResourceCollection($resolutions);

        return $this->jsonSuccessResponse($data, $this->http_code_ok);      
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = [
            'resolution_no' => ['string', 'unique:resolutions'],
            'bokal_id' => 'integer ',
            'date_passed' => 'date',
            'for_referral_id' => 'array',
            'adopt' => 'integer',
            'pdf' => 'required|mimes:pdf|max:10000000'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator->errors();
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();
        try{
            DB::beginTransaction();
            $resolution = new Resolution;
            $resolution->fill($data);
            $resolution->save();

            /**
             * Upload Attachment
             */
            if (isset($data['pdf'])) {
                $folder = config('folders.resolutions');
                $path = "{$folder}/{$resolution->id}";
                // $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
                $filename = $request->file('pdf')->getClientOriginalName();
                $request->file('pdf')->storeAs("public/{$path}", $filename);
                $pdf = "{$path}/{$filename}";
                $resolution->file = $pdf;
                $resolution->save();
            }

            $sync = [];
            $for_referrals = $data['for_referral_id'];
            if(isset($data['adopt'])){
                foreach ($for_referrals as $for_referral) {
                    $status = CommunicationStatus::where('for_referral_id',$for_referral)->get();
                    $status->toQuery()->update([
                        'adopt' => true,
                    ]);
                }
            }else{              
                foreach ($for_referrals as $for_referral) {
                    $status = CommunicationStatus::where('for_referral_id',$for_referral)->get();
                    $status->toQuery()->update([
                        'approved' => true,
                    ]);
                }
            }
            
            
            $resolution->for_referral()->sync($for_referrals);

            DB::commit();
            
            return $this->jsonSuccessResponse(null, $this->http_code_ok, "Resolution succesfully added");

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->jsonFailedResponse(null, $this->http_code_error, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false ) {
            return $this->jsonErrorInvalidParameters();
        }

        $resolution = Resolution::find($id);

        if (is_null($resolution)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new ResolutionResource($resolution);

        return $this->jsonSuccessResponse($data, $this->http_code_ok);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false ) {
            return $this->jsonErrorInvalidParameters();
        }        
        $resolution = Resolution::find($id);

        if (is_null($resolution)) {
			return $this->jsonErrorResourceNotFound();
        }

        $rules = [
            'resolution_no' => ['string', Rule::unique('resolutions')->ignore($resolution),],
            'bokal_id' => 'integer ',
            'date_passed' => 'date',
            'for_referral_id' => 'array',
            'pdf' => 'required|mimes:pdf|max:10000000'
        ];

        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();
        try{
            DB::beginTransaction();
            $resolution->fill($data);
            $resolution->save();

            /**
             * Upload Attachment
             */
            if (isset($data['pdf'])) {
                $folder = config('folders.resolutions');
                $path = "{$folder}/{$resolution->id}";
                // $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
                $filename = $request->file('pdf')->getClientOriginalName();
                $request->file('pdf')->storeAs("public/{$path}", $filename);
                $pdf = "{$path}/{$filename}";
                $resolution->file = $pdf;
                $resolution->save();
            }

            $sync = [];

            $for_referrals = $data['for_referral_id'];
            
            $resolution->for_referral()->sync($for_referrals);
            DB::commit();
            
            return $this->jsonSuccessResponse(null, $this->http_code_ok, "Resolution succesfully updated");

        } catch (\Exception $e) {

            DB::rollBack();

            return $this->jsonFailedResponse(null, $this->http_code_error, $e->getMessage());
        }    
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (filter_var($id, FILTER_VALIDATE_INT) === false ) {
            return $this->jsonErrorInvalidParameters();
        }

        $resolution = Resolution::find($id);

        if (is_null($resolution)) {
			return $this->jsonErrorResourceNotFound();
        }  

        $resolution->delete();

        return $this->jsonDeleteSuccessResponse();         
    }
}
