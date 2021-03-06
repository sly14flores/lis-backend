<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use App\Customs\Messages;
use App\Models\User;

use App\Http\Resources\User\UserResource;
use App\Http\Resources\User\UserListResourceCollection;

class UserController extends Controller
{
    use Messages;

    private $http_code_ok;
    private $http_code_error;    

	public function __construct()
	{
		$this->middleware(['auth:api']);
		// $this->authorizeResource(User::class, User::class);
		
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
        $name = (is_null($filters['name']))?null:$filters['name'];
        $group_id = (is_null($filters['group_id']))?null:$filters['group_id'];
        $email = (is_null($filters['email']))?null:$filters['email'];

        $wheres = [];

        if ($group_id!=null) {
            $wheres[] = ['group_id', $group_id];
        }

        if ($email!=null) {
            $wheres[] = ['email', 'LIKE', "%{$email}%"];
        }

        $users = User::select('*',DB::raw("CONCAT(`firstname`,' ',`middlename`,' ', `lastname`) as full_name"))->where($wheres);

        if ($name!=null) {
            $users->having('full_name', 'LIKE', "%{$name}%");
        }
        
        $users = $users->latest()->paginate(10);

        $data = new UserListResourceCollection($users);

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
            'firstname' => 'string',
            'middlename' => 'string',
            'lastname' => 'string',
            'email' => ['string', 'email', 'unique:users'],
            'group_id' => 'integer',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();
        
        $user = new User;
        
        $password = Hash::make(env('DEFAULT_PASSWORD','12345678'));
        $data['password'] = $password;

		$user->fill($data);
        
        $user->save();

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "User succesfully added");        
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

        $user = User::find($id);

        if (is_null($user)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new UserResource($user);

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

        $user = User::find($id);

        if (is_null($user)) {
			return $this->jsonErrorResourceNotFound();
        }

        $rules = [
            'firstname' => 'string',
            'middlename' => 'string',
            'lastname' => 'string',
            'email' => ['string', 'email', Rule::unique('users')->ignore($user)],
            'group_id' => 'integer',
        ];
        
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();
        $user->fill($data);
        $user->save();

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "User info succesfully updated");        
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

        $user = User::find($id);

        if (is_null($user)) {
			return $this->jsonErrorResourceNotFound();
        }  

        $user->delete();

        return $this->jsonDeleteSuccessResponse();         
    }

    private function rules()
    {
        return [
            'firstname' => 'string',
            'middlename' => 'string',
            'lastname' => 'string',
            'email' => ['string', 'email', 'unique:users'],
            'group_id' => 'integer',
        ];
    }
}
