<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

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
    public function index(Request $request)
    {
        $filters = $request->all();
        $for_referral_id = (is_null($filters['for_referral_id']))?null:$filters['for_referral_id'];
        $meeting_date = (is_null($filters['meeting_date']))?null:$filters['meeting_date'];
        $subject = (is_null($filters['subject']))?null:$filters['subject'];
        $lead_committee_id = (is_null($filters['lead_committee_id']))?null:$filters['lead_committee_id'];
        $joint_committee_id = (is_null($filters['joint_committee_id']))?null:$filters['joint_committee_id'];
        $category_id = (is_null($filters['category_id']))?null:$filters['category_id'];
		$origin_id = (is_null($filters['origin_id']))?null:$filters['origin_id'];

        $wheres = [];
        if ($for_referral_id!=null) {
            $wheres[] = ['for_referral_id', 'LIKE', "%{$for_referral_id}%"];
        }

        if ($meeting_date!=null) {
            $wheres[] = ['meeting_date', 'LIKE', "%{$meeting_date}%"];
        }

        $meeting = CommitteeMeeting::where($wheres);

        if ($subject!=null) {
			$meeting->whereHas('for_referrals', function(Builder $query) use ($subject) {
				$query->where([['for_referrals.subject','LIKE', "%{$subject}%"]]);
			});
		}

        if ($category_id!=null) {
			$meeting->whereHas('for_referrals', function(Builder $query) use ($category_id) {
				$query->where([['for_referrals.category_id', $category_id]]);
			});
		}

        if ($origin_id!=null) {
			$meeting->whereHas('for_referrals', function(Builder $query) use ($origin_id) {
				$query->where([['for_referrals.origin_id', $origin_id]]);
			});
		}

        if ($lead_committee_id!=null) {
			$meeting->whereHas('for_referrals.committees', function(Builder $query) use ($lead_committee_id) {
				$query->where([['committee_for_referral.committee_id', $lead_committee_id],['committee_for_referral.lead_committee',true]]);
			});
		}
		if ($joint_committee_id!=null) {
			$meeting->whereHas('for_referrals.committees', function(Builder $query) use ($joint_committee_id) {
				$query->where([['committee_for_referral.committee_id', $joint_committee_id],['committee_for_referral.joint_committee',true]]);
			});
		}

        $meeting = $meeting->orderBy('id','desc')->paginate(10);

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
