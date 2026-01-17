<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $category = ProductCategory::latest()->get();
       return view('admin.backend.category.index',compact('category'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      $request->validate([
        'category_name' => ['required','unique:product_categories,category_name'],
      ]);

      $category = new ProductCategory();
      $category->category_name = $request->category_name;
      $category->category_slug = strtolower(str_replace(' ','-',$request->category_name));
      $category->save();

      $notification = array(
        'message' => 'Category Inserted Successfully',
        'alert-type' => 'success'
      );
        return redirect()->back()->with($notification);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
   public function edit($id)
{
    $category = ProductCategory::findOrFail($id);
     if ($category) {
        return response()->json($category);
    }

    // Agar na mile to error bhej dein
    return response()->json(['error' => 'Category not found'], 404);
}



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       $request->validate([
    'category_name' => 'required|unique:product_categories,category_name,'.$id,
]);

      $category =  ProductCategory::findOrFail($id);
      $category->category_name = $request->category_name;
      $category->category_slug = strtolower(str_replace(' ','-',$request->category_name));
      $category->save();

      $notification = array(
        'message' => 'Category Updated Successfully',
        'alert-type' => 'success'
      );
        return redirect()->back()->with($notification);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        
        $category = ProductCategory::findOrFail($id);
        $category->delete();
        $notification = array(
            'message' => 'Category Deleted Successfully',
            'alert-type' => 'success'
          );
            return redirect()->back()->with($notification);
    }
}
