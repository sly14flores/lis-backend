<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Customs\Messages;
use App\Models\Category;

use App\Http\Resources\Category\CategoryResource;
use App\Http\Resources\Category\CategoryListResourceCollection;

class CategoryController extends Controller
{
    use Messages;

    private $http_code_ok;
    private $http_code_error;    

	public function __construct()
	{
		$this->middleware(['auth:api']);
		// $this->authorizeResource(Category::class, Category::class);
		
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

        $wheres = [];

        if ($name!=null) {
            $wheres[] = ['name', 'LIKE', "%{$name}%"];
        }

        $categories = Category::where($wheres)->orderBy('id','desc')->paginate(10);

        $data = new CategoryListResourceCollection($categories);

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
            'name' => ['string', 'unique:categories'],
        ];

        $customMessages = [
            'name.unique' => 'Category Name is already taken'
        ];

        $validator = Validator::make($request->all(), $rules, $customMessages);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation($validator->errors());
        }

        $data = $validator->valid();
        
        $category = new Category;
		$category->fill($data);
        $category->save();

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "Category succesfully added");
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

        $category = Category::find($id);

        if (is_null($category)) {
			return $this->jsonErrorResourceNotFound();
        }

		$data = new CategoryResource($category);

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

        $category = Category::find($id);
        
        if (is_null($category)) {
			return $this->jsonErrorResourceNotFound();
        }

        $rules = [
            'name' => ['string', Rule::unique('categories')->ignore($category),]
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->jsonErrorDataValidation();
        }

        $data = $validator->valid();
        $category->fill($data);
        $category->save();

        return $this->jsonSuccessResponse(null, $this->http_code_ok, "Category succesfully updated");
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

        $category = Category::find($id);

        if (is_null($category)) {
			return $this->jsonErrorResourceNotFound();
        }  

        $category->delete();

        return $this->jsonDeleteSuccessResponse();         
    }
}
