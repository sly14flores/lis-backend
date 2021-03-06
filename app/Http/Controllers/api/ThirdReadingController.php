<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;

use App\Customs\Messages;
use App\Models\ThirdReading;
use App\Models\CommunicationStatus;

use App\Http\Resources\ThirdReading\ThirdReadingResource;
use App\Http\Resources\ThirdReading\ThirdReadingListResourceCollection;

class ThirdReadingController extends Controller
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
        $for_referral_id = (is_null($filters['for_referral_id']))?null:$filters['for_referral_id'];
        $subject = (is_null($filters['subject']))?null:$filters['subject'];
        $date_received = (is_null($filters['date_received']))?null:$filters['date_received'];
        $agenda_date = (is_null($filters['agenda_date']))?null:$filters['agenda_date'];

        $wheres = [];
        
        if ($for_referral_id!=null) {
            $wheres[] = ['for_referral_id','LIKE', "%{$for_referral_id}%"];
        }

        if ($date_received!=null) {
            $wheres[] = ['date_received', $date_received];
        }

        if ($agenda_date!=null) {
            $wheres[] = ['agenda_date', $agenda_date];
        }

        $wheres[] = ['archive', 0];

        $third_readings = ThirdReading::where($wheres);

        if ($subject!=null) {
			$third_readings->whereHas('for_referral', function(Builder $query) use ($subject) {
				$query->where([['for_referrals.subject','LIKE', "%{$subject}%"]]);
			});
		}

        $third_readings = $third_readings->latest()->paginate(10);

        $data = new ThirdReadingListResourceCollection($third_readings);

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
            'for_referral_id' => ['integer', 'unique:third_readings'],
            'date_received' => 'date',
            'agenda_date' => 'date',
            'pdf' => 'required|mimes:pdf|max:10000000'
        ];

        $customMessages = [
            'for_referral_id.unique' => 'Third Reading is already existing'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) { 
            return $this->jsonErrorDataValidation($validator->errors());
        }

        $data = $validator->valid();
        
        $third_reading = new ThirdReading;
		$third_reading->fill($data);
        $third_reading->save();

        /**
         * Upload Attachment
         */
        if (isset($data['pdf'])) {
            $folder = config('folders.third_reading');
            $path = "{$folder}/{$third_reading->id}";
            // $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
            $filename = $request->file('pdf')->getClientOriginalName();
            $request->file('pdf')->storeAs("public/{$path}", $filename);
            $pdf = "{$path}/{$filename}";
            $third_reading->file = $pdf;
            $third_reading->save();
        }

        $status = CommunicationStatus::where('for_referral_id',$third_reading->for_referral_id)->get();
        $status->toQuery()->update([
            'passed' => true,
        ]);

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "Third Reading succesfully added");
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

        $third_reading = ThirdReading::find($id);

        if (is_null($third_reading)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new ThirdReadingResource($third_reading);

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
            'date_received' => 'date',
            'agenda_date' => 'date',
        ];

        $third_reading = ThirdReading::find($id);

        if (is_null($third_reading)) {
			return $this->jsonErrorResourceNotFound();
        }
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $validator->errors();
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();
        $third_reading->fill($data);
        $third_reading->save();

        /**
         * Upload Attachment
         */
        if (isset($data['pdf'])) {
            $folder = config('folders.third_reading');
            $path = "{$folder}/{$third_reading->id}";
            // $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
            $filename = $request->file('pdf')->getClientOriginalName();
            $request->file('pdf')->storeAs("public/{$path}", $filename);
            $pdf = "{$path}/{$filename}";
            $third_reading->file = $pdf;
            $third_reading->save();
        }

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "Third Reading succesfully updated");        
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

        $third_reading = ThirdReading::find($id);

        if (is_null($third_reading)) {
			return $this->jsonErrorResourceNotFound();
        }  

        $third_reading->delete();

        return $this->jsonDeleteSuccessResponse();         
    }
}
