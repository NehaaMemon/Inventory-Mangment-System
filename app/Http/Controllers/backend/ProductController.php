<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\WareHouse;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;

use Intervention\Image\Drivers\Gd\Driver;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
       $products = Product::latest()->get();
        return view('admin.backend.product.index',compact('products'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $categories = ProductCategory::all();
        $suppliers = Supplier::all();
        $warehouses = WareHouse::all();
        $brands = Brand::all();
        $products = Product::all();
        return view('admin.backend.product.create',
        compact('categories','suppliers','warehouses','brands',
        'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
{
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'code' => ['required', 'unique:products,code'],
        'category_id' => ['required', 'exists:product_categories,id'],
        'brand_id' => ['required', 'exists:brands,id'],
        'warehouse_id' => ['required', 'exists:ware_houses,id'],
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'price' => ['required','decimal:2'],
        'discount_price' => ['required','decimal:2'],
        'stock_alert' => ['required','numeric'],
        'note' => ['nullable','string'],
        'product_qty' => ['required','numeric'],
        'status' => ['required','string'],

        // MULTIPLE IMAGES
        'image' => ['required'],
        'image.*' => ['image','mimes:jpg,jpeg,png','max:2048'],
    ]);

    $product = new Product();
    $product->name = $request->name;
    $product->code = $request->code;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;
    $product->warehouse_id = $request->warehouse_id;
    $product->supplier_id = $request->supplier_id;
    $product->price = $request->price;
    $product->discount_price = $request->discount_price;
    $product->stock_alert = $request->stock_alert;
    $product->note = $request->note;
    $product->product_qty = $request->product_qty;
    $product->status = $request->status;



    // ---------------- PROCESS IMAGES ----------------
    if ($request->hasFile('image')) {

        $manager = new ImageManager(new Driver());
        $files = $request->file('image');

        foreach ($files as $index => $img) {

            $newName = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();

            $image = $manager->read($img);
            $image->resize(150,150)->save(public_path('upload/product/'.$newName));

            $path = 'upload/product/'.$newName;

            // 👉 SET FIRST IMAGE AS MAIN IMAGE
            if ($index === 0) {
                $product->image = $path;
                $product->save();
            }

            // Save to gallery table
            ProductImage::create([
                'product_id' => $product->id,
                'image' => $path,
            ]);
        }
    }

      $notify = array(
                'message' => 'Product Added Successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('product.index')->with($notify);
}




    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $product = Product::findOrFail($id);
       return view('admin.backend.product.show',compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $editProduct = Product::findOrFail($id);
        $categories = ProductCategory::all();
        $suppliers = Supplier::all();
        $warehouses = WareHouse::all();
        $brands = Brand::all();
        $mltiImages = ProductImage::where('product_id',$id)->get();
        return view('admin.backend.product.edit',
        compact('editProduct','categories','suppliers','warehouses','brands',
        'mltiImages'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'code' => ['required'],
        'category_id' => ['required', 'exists:product_categories,id'],
        'brand_id' => ['required', 'exists:brands,id'],
        'warehouse_id' => ['required', 'exists:ware_houses,id'],
        'supplier_id' => ['required', 'exists:suppliers,id'],
        'price' => ['required','decimal:2'],
        'discount_price' => ['required','decimal:2'],
        'stock_alert' => ['required','numeric'],
        'note' => ['nullable','string'],
        'product_qty' => ['required','numeric'],
        'status' => ['required','string'],

        // MULTIPLE IMAGES

        'image.*' => ['image','mimes:jpg,jpeg,png','max:2048'],
    ]);

    $product = Product::findOrFail($id);
    $product->name = $request->name;
    $product->code = $request->code;
    $product->category_id = $request->category_id;
    $product->brand_id = $request->brand_id;
    $product->warehouse_id = $request->warehouse_id;
    $product->supplier_id = $request->supplier_id;
    $product->price = $request->price;
    $product->discount_price = $request->discount_price;
    $product->stock_alert = $request->stock_alert;
    $product->note = $request->note;
    $product->product_qty = $request->product_qty;
    $product->status = $request->status;

    // ---------------- PROCESS IMAGES ----------------
    // DELETE REMOVED IMAGES
if ($request->has('remove_image')) {
    foreach ($request->remove_image as $removeImageId) {
        $img = ProductImage::find($removeImageId);
        if ($img) {
            if (file_exists(public_path($img->image))) {
                unlink(public_path($img->image));
            }
            $img->delete();
        }
    }
}

// ADD NEW IMAGES
if ($request->hasFile('image')) {
    $manager = new ImageManager(new Driver());
    $files = $request->file('image');

    foreach ($files as $index => $img) {
        $newName = hexdec(uniqid()).'.'.$img->getClientOriginalExtension();
        $image = $manager->read($img);
        $image->resize(150,150)->save(public_path('upload/product/'.$newName));
        $path = 'upload/product/'.$newName;

        $product->images()->create([
            'image' => $path,
        ]);

        // Agar first image product->image field me bhi chahiye:
        if ($index === 0) {
            $product->image = $path;
            $product->save();
        }
    }
}


      $notify = array(
                'message' => 'Product Updated Successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('product.index')->with($notify);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
       try{
        $product = Product::findOrFail($id);


        // Delete main image
        if (file_exists(public_path($product->image))) {
            unlink(public_path($product->image));
        }

        // Delete gallery images
        $galleryImages = ProductImage::where('product_id', $id)->get();
        foreach ($galleryImages as $img) {
            if (file_exists(public_path($img->image))) {
                unlink(public_path($img->image));
            }
            $img->delete();
        }

        $product->delete();

        $notify = array(
            'message' => 'Product Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('product.index')->with($notify);

       }
       catch(\Exception $e){
        $notify = array(
            'message' => 'Error Occurred While Deleting Product',
            'alert-type' => 'error'
        );
        return redirect()->route('product.index')->with($notify);
       }
    }
}
