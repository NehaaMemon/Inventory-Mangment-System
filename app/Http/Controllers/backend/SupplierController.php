<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function allSupplier()
    {
        $supplier = Supplier::latest()->get();
       return view('admin.backend.supplier.all_supplier',compact('supplier'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function addSupplier()
    {
        $supplier = Supplier::latest()->take(5)->get();
        return view('admin.backend.supplier.add_supplier',compact('supplier'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'max:250'],
            'email' => ['required','email','unique:suppliers','email'],
            'phone' => ['required','regex:/^\+?\d{7,15}$/'],
            'address' => ['required', 'max:500'],
             ], [
        'email.unique' => 'This email is already taken by another customer.'
    ]);

        $supplier = new Supplier();
        $supplier->name = $request->name;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->save();
        $notify = array(
            'message' => 'Supplier Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.supplier')->with($notify);
    }
            public function checkEmail(Request $request)
        {
            $exists = Supplier::where('email', $request->email)->exists();
            return response()->json(['exists' => $exists]);
        }



    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $supplier = Supplier::findOrFail($id);
        return view('admin.backend.supplier.edit_supplier',compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $supplier = Supplier::findOrFail($id);
        $request->validate([
            'name' => ['required', 'max:250'],
           'email' => ['required', 'email', 'unique:suppliers,email,'.$supplier->id],
            'phone' => ['required','regex:/^\+?\d{7,15}$/'],
            'address' => ['required', 'max:500' ]
        ], [
        'email.unique' => 'This email is already taken by another customer.'
    ]);

        $supplier->name = $request->name;
        $supplier->email = $request->email;
        $supplier->phone = $request->phone;
        $supplier->address = $request->address;
        $supplier->save();
        $notify = array(
            'message' => 'Supplier Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('all.supplier')->with($notify);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try{
            $supplier = Supplier::findOrFail($id);
            $supplier->delete();
            $notify = array(
                'message' => 'Supplier Deleted Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('all.supplier')->with($notify);

        }
        catch(\Exception $e){
            $notify = array(
                'message' => 'Something went wrong',
                'alert-type' => 'error'
            );
            return redirect()->route('all.supplier')->with($notify);
        }
    }
}
