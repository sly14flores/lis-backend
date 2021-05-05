<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Customs\Messages;
use App\Models\Ordinance;
use App\Models\CommunicationStatus;

use App\Http\Resources\Ordinance\OrdinanceResource;
use App\Http\Resources\Ordinance\OrdinanceListResourceCollection;


class OrdinanceController extends Controller
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
    public function index()
    {
        $ordinances = Ordinance::paginate(10);

        $data = new OrdinanceListResourceCollection($ordinances);

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
            'for_referral_id' => 'integer',
            'title' => 'string',
            'amending' => 'integer',
            'date_passed' => 'date',
            'date_signed' => 'date',
            'authors' => 'array',
            'co_authors' => 'array',
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

            $ordinance = new Ordinance;
            $ordinance->fill($data);
            $ordinance->save();
    
            // /**
            //  * Upload Attachment
            //  */
            if (isset($data['pdf'])) {
                $folder = config('folders.ordinances');
                $path = "{$folder}/{$ordinance->id}";
                $filename = $request->file('pdf')->getClientOriginalName();
                $request->file('pdf')->storeAs("public/{$path}", $filename);
                $pdf = "{$path}/{$filename}";
                $ordinance->file = $pdf;
                $ordinance->save();
            }

            $status = CommunicationStatus::where('for_referral_id',$ordinance->for_referral_id)->get();
            $status->toQuery()->update([
                'approved' => true,
            ]);

            // Sync in pivot table
            $authors = $data['authors'];
            $co_authors = $data['co_authors'];
            $syncs = [];

            //authors
            foreach ($authors as $author) {
                $syncs[$author['id']] = [
                    'author' => true,
                    'co_author' =>false,
                ];
            }

            //co-authors
            foreach ($co_authors as $co_author) {
                $syncs[$co_author['id']] = [
                    'author' => false,
                    'co_author' =>true,
                ];
            }

            $ordinance->bokals()->sync($syncs);

            DB::commit();

            return $this->jsonSuccessResponse(null, $this->http_code_ok, "Ordinance succesfully added");

        }catch (\Exception $e) {

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

        $ordinance = Ordinance::find($id);

        if (is_null($ordinance)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new OrdinanceResource($ordinance);

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

        $rules = [
            'for_referral_id' => 'integer',
            'title' => 'string',
            'amending' => 'integer',
            'date_passed' => 'date',
            'date_signed' => 'date',
            'authors' => 'array',
            'co_authors' => 'array',
        ];

        $ordinance = Ordinance::find($id);

        if (is_null($ordinance)) {
			return $this->jsonErrorResourceNotFound();
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();

        try{

            DB::beginTransaction();
            $ordinance->fill($data);
            $ordinance->save();

            /**
             * Upload Attachment
             */
            if (isset($data['pdf'])) {
                $folder = config('folders.ordinances');
                $path = "{$folder}/{$ordinance->id}";
                // $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
                $filename = $request->file('pdf')->getClientOriginalName();
                $request->file('pdf')->storeAs("public/{$path}", $filename);
                $pdf = "{$path}/{$filename}";
                $ordinance->file = $pdf;
                $ordinance->save();
            }

            // Sync in pivot table
            $authors = $data['authors'];
            $co_authors = $data['co_authors'];
            $syncs = [];

            //authors
            foreach ($authors as $author) {
                $syncs[$author['id']] = [
                    'author' => true,
                    'co_author' =>false,
                ];
            }

            //co-authors
            foreach ($co_authors as $co_author) {
                $syncs[$co_author['id']] = [
                    'author' => false,
                    'co_author' =>true,
                ];
            }

            $ordinance->bokals()->sync($syncs);

            DB::commit();

            return $this->jsonSuccessResponse(null, $this->http_code_ok, "Ordinance succesfully updated");

        }catch (\Exception $e) {
            
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

        $ordinance = Ordinance::find($id);

        if (is_null($ordinance)) {
			return $this->jsonErrorResourceNotFound();
        }  

        $ordinance->delete();

        return $this->jsonDeleteSuccessResponse();         
    }
}
