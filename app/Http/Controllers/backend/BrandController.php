<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BrandController extends Controller
{
    function allBrand(): View
    {
        $brand = Brand::latest()->get();
        return view('admin.backend.brand.all_brand', compact('brand'));
    }

    function addBrand(): View
    {
        $brand = Brand::latest()->take(5)->get();
        return view('admin.backend.brand.add_brand', compact('brand'));
    }


    function storeBrand(Request $request)
    {

        $request->validate([
            'name' => ['required', 'max:250'],
            'image' => ['required', 'image', 'max:3000']
        ]);
        if ($request->file('image')) {
            $image = $request->file('image');
            $manager = new ImageManager(new Driver());
            $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
            $img = $manager->read($image);
            $img->resize(100, 90)->save(public_path('upload/brand/' . $name_gen));
            $save_url = 'upload/brand/' . $name_gen;

            Brand::create([
                'name' => $request->name,
                'image' => $save_url

            ]);
            $notify = array(
                'message' => 'Brand Added Successfully',
                'alert-type' => 'success'

            );
            return redirect()->route('all.brand')->with($notify);
        }
    }
    //End Method

    public function editBrand(string $id)
    {
        $brand = Brand::find($id);
        return view('admin.backend.brand.edit_brand', compact('brand'));
    }

   public function updateBrand(Request $request, string $id)
{
    $request->validate([
        'name' => ['required', 'max:250'],
        'image' => ['nullable', 'image', 'max:3000']
    ]);

    $brand = Brand::findOrFail($id);

    if ($request->file('image')) {
        $image = $request->file('image');
        $manager = new ImageManager(new Driver());
        $name_gen = hexdec(uniqid()) . '.' . $image->getClientOriginalExtension();
        $img = $manager->read($image);
        $img->resize(100, 90)->save(public_path('upload/brand/' . $name_gen));
        $save_url = 'upload/brand/' . $name_gen;

        if ($brand->image && file_exists(public_path($brand->image))) {
            @unlink(public_path($brand->image));
        }

        $brand->name = $request->name;
        $brand->image = $save_url;
        $brand->save();

        $notify = [
            'message' => 'Brand updated with new image successfully',
            'alert-type' => 'success'
        ];
    } else {
        $brand->name = $request->name;
        $brand->save();

        $notify = [
            'message' => 'Brand updated without image successfully',
            'alert-type' => 'success'
        ];
    }

    return redirect()->route('all.brand')->with($notify);
}

function deleteBrand(string $id)
{

    try{
        $brand = Brand::findOrFail($id);
    if ($brand->image && file_exists(public_path($brand->image))) {
        @unlink(public_path($brand->image));
    }

    $brand->delete();

    $notify = [
        'message' => 'Brand deleted successfully',
        'alert-type' => 'success'
    ];

    return redirect()->route('all.brand')->with($notify);
    }
    catch(\Exception $e){
        $notify = [
            'message' => 'Brand not found',
            'alert-type' => 'error'
        ];

        return redirect()->back()->with($notify);
    }

}
}
    //End Method
