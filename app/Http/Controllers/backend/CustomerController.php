<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    public function index()
    {
       $customer = Customer::latest()->get();
       return view('admin.backend.customer.index',compact('customer'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customer = Customer::latest()->take(5)->get();
        return view('admin.backend.customer.create',compact('customer'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      $request->validate([
            'name' => ['required', 'max:250'],
            'email' => ['required','email','unique:customers','email'],
            'phone' => ['required','regex:/^\+?\d{7,15}$/'],
            'address' => ['required', 'max:500']
             ], [
        'email.unique' => 'This email is already taken .'
    ]);

        $customer = new Customer();
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->save();
        $notify = array(
            'message' => 'Customer Added Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('customers.index')->with($notify);
    }
     public function checkEmail(Request $request)
    {
        $exists = Customer::where('email', $request->email)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Display the specified resource.
     */

public function show()
{

}


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
       $customer = Customer::findOrFail($id);
       return view('admin.backend.customer.edit',compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $customer = Customer::findOrFail($id);
        $request->validate([
            'name' => ['required', 'max:250'],
            'email' => ['required', 'email', 'unique:customers,email,'.$customer->id],
            'phone' => ['required','regex:/^\+?\d{7,15}$/'],
            'address' => ['required', 'max:500']
       ], [
        'email.unique' => 'This email is already taken .'
    ]);
        $customer->name = $request->name;
        $customer->email = $request->email;
        $customer->phone = $request->phone;
        $customer->address = $request->address;
        $customer->save();
        $notify = array(
            'message' => 'Customer Updated Successfully',
            'alert-type' => 'success'
        );
        return redirect()->route('customers.index')->with($notify);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $customer = Customer::findOrFail($id);
            $customer->delete();
            $notify = array(
                'message' => 'Customer Deleted Successfully',
                'alert-type' => 'success'
            );
            return redirect()->route('customers.index')->with($notify);
        }
        catch(\Exception $e){
           $notify = array(
                'message' => 'Something went wrong',
                'alert-type' => 'error'
            );
            return redirect()->route('customers.index')->with($notify);
        }
    }
}
