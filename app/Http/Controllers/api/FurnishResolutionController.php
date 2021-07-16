<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\Storage;

use App\Customs\Messages;
use App\Models\Resolution;

use App\Http\Resources\FurnishResolution\FurnishResolutionResource;
use App\Http\Resources\FurnishResolution\FurnishResolutionListResourceCollection;

class FurnishResolutionController extends Controller
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

        $furnishResolutions = Resolution::where($wheres);
        $furnishResolutions = $furnishResolutions->orderBy('id','desc')->paginate(10);
        $data = new FurnishResolutionListResourceCollection($furnishResolutions);

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
            'resolution_id' => 'integer',
            'origin_id' => 'array',
            'date_furnished' => 'date',
		];

        $validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return $this->jsonErrorDataValidation($validator->errors());
		}

		$data = $validator->valid();
        try {

            DB::beginTransaction();
            // Sync in pivot table
			$syncs = [];
            $origins = $data['origins'];
            $date_furnished = $data['date_furnished'];
            foreach ($origins as $origin) {
                $syncs[$origin] = [
                    'date_furnished' => $date_furnished,
                ];
            }
            $furnish = Resolution::find($data['resolution_id']);
            $furnish->origins()->sync($syncs);

            DB::commit();

            return $this->jsonSuccessResponse($furnish, $this->http_code_ok, "Success");
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

		$furnishResolution = FurnishResolution::find($id);

		if (is_null($furnishResolution)) {
			return $this->jsonErrorResourceNotFound();
		}

		$data = new FurnishResolutionResource($furnishResolution);

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
            // 'resolution_id' => 'integer',
            'origin_id' => 'array',
            'date_furnished' => 'date',
		];

        $furnish = Resolution::find($id);

        if (is_null($furnish)) {
			return $this->jsonErrorResourceNotFound();
		}

        $validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return $this->jsonErrorDataValidation($validator->errors());
		}

		$data = $validator->valid();
        try {

            DB::beginTransaction();

            // Sync in pivot table
			$syncs = [];
            $origins = $data['origins'];
            $date_furnished = $data['date_furnished'];
            foreach ($origins as $origin) {
                $syncs[$origin] = [
                    'date_furnished' => $date_furnished,
                ];
            }
            $furnish->origins()->sync($syncs);

            DB::commit();

            return $this->jsonSuccessResponse(null, $this->http_code_ok, "Success");
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

		$furnishResolution = FurnishResolution::find($id);

		if (is_null($furnishResolution)) {
			return $this->jsonErrorResourceNotFound();
		}  

		$furnishResolution->delete();

		return $this->jsonDeleteSuccessResponse();
    }
}
