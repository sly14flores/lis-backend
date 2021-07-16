<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
// use Illuminate\Support\Facades\DB;
// use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Support\Facades\Storage;

use App\Customs\Messages;
use App\Models\FurnishOrdinance;

use App\Http\Resources\FurnishOrdinance\FurnishOrdinanceResource;
use App\Http\Resources\FurnishOrdinance\FurnishOrdinanceListResourceCollection;

class FurnishOrdinanceController extends Controller
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

        $furnishOrdinances = FurnishOrdinance::where($wheres);
        $furnishOrdinances = $furnishOrdinances->orderBy('id','desc')->paginate(10);
        $data = new FurnishOrdinanceListResourceCollection($furnishOrdinances);

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
            'ordinance_id' => 'integer',
            'origin_id' => 'integer',
            'date_furnished' => 'date',
		];

        $validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return $this->jsonErrorDataValidation($validator->errors());
		}

		$data = $validator->valid();

        $furnishOrdinance = new FurnishOrdinance;
        $furnishOrdinance->fill($data);
        $furnishOrdinance->save();
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

		$furnishOrdinance = FurnishOrdinance::find($id);

		if (is_null($furnishOrdinance)) {
			return $this->jsonErrorResourceNotFound();
		}

		$data = new FurnishOrdinanceResource($furnishOrdinance);

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
            'ordinance_id' => 'integer',
            'origin_id' => 'integer',
            'date_furnished' => 'date',
		];

        $furnishOrdinance = FurnishOrdinance::find($id);

		if (is_null($furnishOrdinance)) {
			return $this->jsonErrorResourceNotFound();
		}

		$validator = Validator::make($request->all(), $rules);

		if ($validator->fails()) {
			return $this->jsonErrorDataValidation($validator->errors());
		}

		$data = $validator->valid();

        $furnishOrdinance->fill($data);
        $furnishOrdinance->save();

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

		$furnishOrdinance = FurnishOrdinance::find($id);

		if (is_null($furnishOrdinance)) {
			return $this->jsonErrorResourceNotFound();
		}  

		$furnishOrdinance->delete();

		return $this->jsonDeleteSuccessResponse();
    }
}
