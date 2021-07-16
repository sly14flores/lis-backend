<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Customs\Messages;
use App\Models\CommitteeMeeting;

use App\Http\Resources\CommitteeMeeting\CommitteeMeetingResource;
use App\Http\Resources\CommitteeMeeting\CommitteeMeetingListResourceCollection;

class CommitteeMeetingController extends Controller
{

    use Messages;

    private $http_code_ok;
    private $http_code_error;    

	public function __construct()
	{
		$this->middleware(['auth:api']);
		// $this->authorizeResource(Agency::class, Agency::class);
		
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
        $filters = $request->all();
        $for_referral_id = (is_null($filters['for_referral_id']))?null:$filters['for_referral_id'];
        $meeting_date = (is_null($filters['meeting_date']))?null:$filters['meeting_date'];

        $wheres = [];
        if ($for_referral_id!=null) {
            $wheres[] = ['for_referral_id', 'LIKE', "%{$for_referral_id}%"];
        }

        if ($meeting_date!=null) {
            $wheres[] = ['meeting_date', 'LIKE', "%{$meeting_date}%"];
        }

        $meeting = CommitteeMeeting::where($wheres)->orderBy('id','desc')->paginate(10);

        $data = new CommitteeMeetingListResourceCollection($meeting);

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
            'meeting_date' => 'date'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation($validator->errors());
        }

        $data = $validator->valid();
        
        $meeting = new CommitteeMeeting;
		$meeting->fill($data);
        $meeting->save();

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "Success");
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

        $meeting = CommitteeMeeting::find($id);

        if (is_null($meeting)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new CommitteeMeetingResource($meeting);

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

        $meeting = CommitteeMeeting::find($id);

        if (is_null($meeting)) {
			return $this->jsonErrorResourceNotFound();
        }

        $rules = [
            'for_referral_id' => 'integer',
            'meeting_date' => 'date'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation($validator->errors());
        }

        $data = $validator->valid();
        $meeting->fill($data);
        $meeting->save();

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "Succesfully updated");
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

        $meeting = CommitteeMeeting::find($id);

        if (is_null($meeting)) {
			return $this->jsonErrorResourceNotFound();
        }

        $meeting->delete();

        return $this->jsonDeleteSuccessResponse(); 
    }
}
