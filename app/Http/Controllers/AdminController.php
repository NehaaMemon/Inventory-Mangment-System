<?php

namespace App\Http\Controllers;

use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }

    function AdminLogin(Request $request)
    {
        $credent = $request->only('email', 'password');
        if (Auth::attempt($credent)) {
            $user = Auth::user();
            $verificationcode = random_int(100000, 999999);

            session(['verification_code' => $verificationcode, 'user_id' => $user->id]);

            Mail::to($user->email)->send(new VerificationCodeMail($verificationcode));
            Auth::logout();

            return redirect()->route('custom.verification.Form')->with('status', 'Verification sent to your mail');
        } else {
            return redirect()->back()->withErrors(['error', 'Invalid Credentials Provided']);
        }
    }

    function showVerification() {
        return view('auth.verify');

    }

    function verificationVerify(Request $request)  {
        $request->validate(['code' => 'required|numeric']);

        if($request->code == session('verification_code')){
            Auth::loginUsingId(session('user_id'));

            session()->forget(['verification_code','user_id']);
            return redirect()->intended(('/dashboard'));
        }
        else{
            return redirect()->back()->withErrors(['code'=> 'Invalid verification Code']);
        }
{

}
    }

    function AdminProfile() : View {
        $id = Auth::user()->id;
        $profileData = User::find($id);
        return view('admin.admin_profile',compact('profileData'));
    }

    function ProfileStore(Request $request)  {
        $id = Auth::user()->id;
        $data = User::find($id);
        $data->name = $request->name;
        $data->email = $request->email;
        $data->phone_no = $request->phone_no;
        $data->address = $request->address;

        $oldPhotoData = $data->photo;

        if($request->hasFile('photo')){
            $file = $request->file('photo');
            $fileName = time().'.'.$file->getClientOriginalExtension();
            $file->move(public_path('upload/user_images'),$fileName);
            $data->photo = $fileName;

          if($oldPhotoData && $oldPhotoData !== $fileName){
            $this->deleteOldPhoto($oldPhotoData);
          }

        }
       $notify = array(
        'message' => 'Admin Profile Updated Successfully',
        'alert-type' => 'success'
       );
        $data->save();
        return redirect()->back()->with($notify);



    }
    private function deleteOldPhoto(string $oldPhotoData) : void {
        $fullPath = public_path('upload/user_images/'.$oldPhotoData);
        if(file_exists($oldPhotoData)){
            unlink($fullPath);
        }
    }

    function AdminPasswordUpdate(Request $request)  {
        $user =Auth::user();
        $request->validate([
            'old_password'=> 'required',
            'new_password'=>'required|min:8|confirmed'
        ]);

        if(!Hash::check($request->old_password,$user->password)){
            $notify = array(
            'message' => 'Current Password Does Not Match',
            'alert-type' => 'error'
            );
            return back()->with($notify);
        }

        User::whereId($user->id)->update([
            'password' => Hash::make($request->new_password)
        ]);
        Auth::logout();


        $notify = array(
        'message' => 'Admin Password Updated Successfully',
        'alert-type' => 'success'
       );
        return redirect()->route('login')->with($notify);
    }
    //password1///

}
