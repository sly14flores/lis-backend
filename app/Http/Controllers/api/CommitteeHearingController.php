<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

use App\Customs\Messages;
use App\Models\CommitteeHearing;

use App\Http\Resources\CommitteeHearing\CommitteeHearingResource;
use App\Http\Resources\CommitteeHearing\CommitteeHearingListResourceCollection;

class CommitteeHearingController extends Controller
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
        $hearing_date = (is_null($filters['hearing_date']))?null:$filters['hearing_date'];
        $lead_committee_id = (is_null($filters['lead_committee_id']))?null:$filters['lead_committee_id'];
        $joint_committee_id = (is_null($filters['joint_committee_id']))?null:$filters['joint_committee_id'];
        $subject = (is_null($filters['subject']))?null:$filters['subject'];
        $category_id = (is_null($filters['category_id']))?null:$filters['category_id'];
		$origin_id = (is_null($filters['origin_id']))?null:$filters['origin_id'];

        $wheres = [];
        if ($for_referral_id!=null) {
            $wheres[] = ['for_referral_id', 'LIKE', "%{$for_referral_id}%"];
        }

        if ($hearing_date!=null) {
            $wheres[] = ['hearing_date', 'LIKE', "%{$hearing_date}%"];
        }

        $hearing = CommitteeHearing::where($wheres);

        if ($subject!=null) {
			$hearing->whereHas('for_referrals', function(Builder $query) use ($subject) {
				$query->where([['for_referrals.subject','LIKE', "%{$subject}%"]]);
			});
		}

        if ($category_id!=null) {
			$hearing->whereHas('for_referrals', function(Builder $query) use ($category_id) {
				$query->where([['for_referrals.category_id', $category_id]]);
			});
		}

        if ($origin_id!=null) {
			$hearing->whereHas('for_referrals', function(Builder $query) use ($origin_id) {
				$query->where([['for_referrals.origin_id', $origin_id]]);
			});
		}

        if ($lead_committee_id!=null) {
			$hearing->whereHas('for_referrals.committees', function(Builder $query) use ($lead_committee_id) {
				$query->where([['committee_for_referral.committee_id', $lead_committee_id],['committee_for_referral.lead_committee',true]]);
			});
		}
		if ($joint_committee_id!=null) {
			$hearing->whereHas('for_referrals.committees', function(Builder $query) use ($joint_committee_id) {
				$query->where([['committee_for_referral.committee_id', $joint_committee_id],['committee_for_referral.joint_committee',true]]);
			});
		}

        $hearing = $hearing->orderBy('id','desc')->paginate(10);

        $data = new CommitteeHearingListResourceCollection($hearing);

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
            'hearing_date' => 'date'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation($validator->errors());
        }

        $data = $validator->valid();
        
        $hearing = new CommitteeHearing;
		$hearing->fill($data);
        $hearing->save();

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

        $hearing = CommitteeHearing::find($id);

        if (is_null($hearing)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new CommitteeHearingResource($hearing);

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

        $hearing = CommitteeHearing::find($id);

        if (is_null($hearing)) {
			return $this->jsonErrorResourceNotFound();
        }

        $rules = [
            'for_referral_id' => 'integer',
            'hearing_date' => 'date'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation($validator->errors());
        }

        $data = $validator->valid();
        $hearing->fill($data);
        $hearing->save();

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

        $hearing = CommitteeHearing::find($id);

        if (is_null($hearing)) {
			return $this->jsonErrorResourceNotFound();
        }

        $hearing->delete();

        return $this->jsonDeleteSuccessResponse(); 
    }
}
