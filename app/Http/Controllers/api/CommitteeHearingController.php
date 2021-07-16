<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

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
    public function index()
    {
        $filters = $request->all();
        $for_referral_id = (is_null($filters['for_referral_id']))?null:$filters['for_referral_id'];
        $hearing_date = (is_null($filters['hearing_date']))?null:$filters['hearing_date'];

        $wheres = [];
        if ($for_referral_id!=null) {
            $wheres[] = ['for_referral_id', 'LIKE', "%{$for_referral_id}%"];
        }

        if ($hearing_date!=null) {
            $wheres[] = ['hearing_date', 'LIKE', "%{$hearing_date}%"];
        }

        $hearing = CommitteeHeairng::where($wheres)->orderBy('id','desc')->paginate(10);

        $data = new CommitteeHeairngListResourceCollection($hearing);

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
        
        $hearing = new CommitteeHeairng;
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

        $hearing = CommitteeHeairng::find($id);

        if (is_null($hearing)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new CommitteeHeairngResource($hearing);

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

        $hearing = CommitteeHeairng::find($id);

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

        $hearing = CommitteeHeairng::find($id);

        if (is_null($hearing)) {
			return $this->jsonErrorResourceNotFound();
        }

        $hearing->delete();

        return $this->jsonDeleteSuccessResponse(); 
    }
}
