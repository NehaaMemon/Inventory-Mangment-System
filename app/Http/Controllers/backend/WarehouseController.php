<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\WareHouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function allWarehouse()
    {

        $warehouse = WareHouse::latest()->get();
        return view('admin.backend.warehouse.all_warehouse',compact('warehouse'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addWarehouse()
    {

        $warehouse = WareHouse::latest()->take(5)->get();
        return view('admin.backend.warehouse.add_warehouse',compact('warehouse'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
            'name' => ['required','max:15'],
            'email' => ['required','email','unique:ware_houses','email'],
            'phone' => ['required','max:20'],
            'city' => ['required','max:20'],
        ]);

        $warehouse = new WareHouse();
        $warehouse->name = $request->name;
        $warehouse->email = $request->email;
        $warehouse->phone = $request->phone;
        $warehouse->city = $request->city;
        $warehouse->save();

        $notify = array(
            'message' => 'Warehouse Added Successfully',
            'alert-type' => 'success'

        );;
        return redirect()->route('all.warehouse')->with($notify);

    }
     public function checkEmail(Request $request)
        {
            $exists = WareHouse::where('email', $request->email)->exists();
            return response()->json(['exists' => $exists]);
        }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

        $warehouse = WareHouse::findOrFail($id);
        return view('admin.backend.warehouse.edit_warehouse',compact('warehouse'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
            $request->validate([
            'name' => ['required','max:15'],
            'email' => ['required','email','unique:warehouses','email'],
            'phone' => ['required','regex:/^\+?\d{7,15}$/'],
            'city' => ['required','max:20'],
        ]);

        $warehouse = WareHouse::findOrFail($id);
        $warehouse->name = $request->name;
        $warehouse->email = $request->email;
        $warehouse->phone = $request->phone;
        $warehouse->city = $request->city;
        $warehouse->save();
        $notify = array(
            'message' => 'Warehouse Updated Successfully',
            'alert-type' => 'success'

        );;
        return redirect()->route('all.warehouse')->with($notify);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $warehouse = WareHouse::findOrFail($id);
        $warehouse->delete();
        $notify = array(
            'message' => 'Warehouse Deleted Successfully',
            'alert-type' => 'success'
        );
        return redirect()->back()->with($notify);
    }
}
