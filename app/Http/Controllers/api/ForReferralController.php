<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

use App\Customs\Messages;
use App\Models\ForReferral;
use App\Models\CommunicationStatus;

use App\Http\Resources\ForReferral\ForReferralResource;
use App\Http\Resources\ForReferral\ForReferralListResourceCollection;

use Carbon\Carbon;

class ForReferralController extends Controller
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
		// return $filters;
		$id = (is_null($filters['id']))?null:$filters['id'];
		$subject = (is_null($filters['subject']))?null:$filters['subject'];
		$date_received = (is_null($filters['date_received']))?null:$filters['date_received'];
		$category_id = (is_null($filters['category_id']))?null:$filters['category_id'];
		$origin_id = (is_null($filters['origin_id']))?null:$filters['origin_id'];
		$lead_committee_id = (is_null($filters['lead_committee_id']))?null:$filters['lead_committee_id'];
		$joint_committee_id = (is_null($filters['joint_committee_id']))?null:$filters['joint_committee_id'];
		$agenda_date = (is_null($filters['agenda_date']))?null:$filters['agenda_date'];

		$wheres = [];
		if ($id!=null) {
			$wheres[] = ['id', $id];
		}
		if ($subject!=null) {
			$wheres[] = ['subject', 'LIKE', "%{$subject}%"];
		}
		if ($date_received!=null) {
			$wheres[] = ['date_received','LIKE', "%{$date_received}%"];
		}
		if ($category_id!=null) {
			$wheres[] = ['category_id', $category_id];
		}
		if ($origin_id!=null) {
			$wheres[] = ['origin_id', $origin_id];
		}
		if ($agenda_date!=null) {
			$wheres[] = ['agenda_date', 'LIKE', "%{$agenda_date}%"];
		}
		$wheres[] = ['archive', 0];

		$for_referrals = ForReferral::where($wheres);
		
		if ($lead_committee_id!=null) {
			$for_referrals->whereHas('committees', function(Builder $query) use ($lead_committee_id) {
				$query->where([['committee_for_referral.committee_id', $lead_committee_id],['committee_for_referral.lead_committee',true]]);
			});
		}
		if ($joint_committee_id!=null) {
			$for_referrals->whereHas('committees', function(Builder $query) use ($joint_committee_id) {
				$query->where([['committee_for_referral.committee_id', $joint_committee_id],['committee_for_referral.joint_committee',true]]);
			});
		}

		$for_referrals = $for_referrals->orderBy('id','desc')->paginate(10);

		$data = new ForReferralListResourceCollection($for_referrals);

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
			'subject' => 'string',
			'date_received' => 'date',
			'category_id' => 'integer',
			'origin_id' => 'integer',
			'agenda_date' => 'date',
			'lead_committee' => 'integer',
			'joint_committees' => 'array',
			'urget' => 'integer',
			'pdf' => 'required|mimes:pdf|max:10000000'
		];

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return $this->jsonErrorDataValidation($validator->errors());
		}

		$data = $validator->valid();

		try {
			
			DB::beginTransaction();
		
			$for_referral = new ForReferral;
			$for_referral->fill($data);
			$for_referral->save();
			$date_received = $data['date_received'];
			// return $date_received;
        	$dueDate = Carbon::parse($date_received);

			/**
			 * Upload Attachment
			 */
			if (isset($data['pdf'])) {
				$folder = config('folders.for_referral');
				$path = "{$folder}/{$for_referral->id}";
				// $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
				$filename = $request->file('pdf')->getClientOriginalName();
				$request->file('pdf')->storeAs("public/{$path}", $filename);
				$pdf = "{$path}/{$filename}";
				$for_referral->file = $pdf;
				$for_referral->save();
			}
			$type = null;
			
			if($data['category_id'] == 1) {
				$type = 1;//Draft Ordinance
				$dueDate = $dueDate->addDays(90);
			}else if($data['category_id'] == 2) {
				$type = 2;//Appropriation Ordinance
				$dueDate = $dueDate->addDays(90);
			}else {
				$type = 3;//Resolution
				$dueDate = $dueDate->addDays(30);
			}
			
			$status = new CommunicationStatus;
			$status->fill([
				'type' => $type
			]);

			$for_referral->comm_status()->save($status);

			//due date
			$for_referral->due_date = $dueDate;
			$for_referral->save();
			// Sync in pivot table
			
			$syncs = [];

			//lead committee
			$syncs[$data['lead_committee']] = [
				'lead_committee' => true,
				'joint_committee' => false,
			];
			
			//joint_committees
			if(isset($data['joint_committees'])) {
				$joint_committees = $data['joint_committees'];
				foreach ($joint_committees as $joint_committee) {
					$syncs[$joint_committee['id']] = [
						'lead_committee' => false,
						'joint_committee' =>true,
					];
				}
			}
			$for_referral->committees()->sync($syncs);
			DB::commit();

			return $this->jsonSuccessResponse(null, $this->http_code_ok, "Communication succesfully added");

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

		$for_referral = ForReferral::find($id);

		if (is_null($for_referral)) {
			return $this->jsonErrorResourceNotFound();
		}

		$data = new ForReferralResource($for_referral);

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
			'subject' => 'string',
			'date_received' => 'date',
			'category_id' => 'integer',
			'origin_id' => 'integer',
			'agenda_date' => 'date',
			'lead_committee' => 'integer',
			'joint_committees' => 'array',
			'urget' => 'boolean',
		];

		$for_referral = ForReferral::find($id);

		if (is_null($for_referral)) {
			return $this->jsonErrorResourceNotFound();
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return $this->jsonErrorDataValidation($validator->errors());
		}

		$data = $validator->valid();
		try {

			DB::beginTransaction();
			$for_referral->fill($data);
			$for_referral->save();

			/**
			 * Upload Attachment
			 */
			if (isset($data['pdf'])) {
				$folder = config('folders.for_referral');
				$path = "{$folder}/{$for_referral->id}";
				// $filename = Str::random(20).".".$request->file('pdf')->getClientOriginalExtension();
				$filename = $request->file('pdf')->getClientOriginalName();
				$request->file('pdf')->storePubliclyAs("public/{$path}", $filename);
				$pdf = "{$path}/{$filename}";
				$for_referral->file = $pdf;
				$for_referral->save();
			}

			if($data['category_id'] == 1) {
				$type = 1;//Draft Ordinance
				// $dueDate = $dueDate->addDays(90);
			}else if($data['category_id'] == 2) {
				$type = 2;//Appropriation Ordinance
				// $dueDate = $dueDate->addDays(90);
			}else {
				$type = 3;//Resolution
				// $dueDate = $dueDate->addDays(30);
			}
			
			$status = CommunicationStatus::where('for_referral_id',$id)->first();
			$status->fill([
				'type' => $type
			]);

			$for_referral->comm_status()->save($status);

			// Sync in pivot table
			$syncs = [];

			//lead committee
			$syncs[$data['lead_committee']] = [
				"lead_committee" => true,
				"joint_committee" => false,
			];

			//joint_committees
			if(isset($data['joint_committees'])) {
				$joint_committees = $data['joint_committees'];
				foreach ($joint_committees as $joint_committee) {
					$syncs[$joint_committee['id']] = [
						'lead_committee' => false,
						'joint_committee' =>true,
					];
				}
			}

			$for_referral->committees()->sync($syncs);
			
			DB::commit();

			return $this->jsonSuccessResponse(null, $this->http_code_ok, "Communication succesfully updated");

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

		$for_referral = ForReferral::find($id);

		if (is_null($for_referral)) {
			return $this->jsonErrorResourceNotFound();
		}  

		$for_referral->delete();

		return $this->jsonDeleteSuccessResponse();         
	}
}
