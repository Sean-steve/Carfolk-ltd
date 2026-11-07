<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class AuthController extends Controller {
 public function show() {return view('admin.login');}
 public function login(Request $request) {
     $data=$request->validate(['email'=>['required','email'],'password'=>['required','string']]);
     if(!Auth::attempt(['email'=>$data['email'],'password'=>$data['password'],'is_admin'=>true,'is_active'=>true])) {
        return back()->withErrors(['email'=>'Invalid account or credentials.'])->onlyInput('email');
     }
     $request->session()->regenerate();
     return redirect()->intended(route('manage.dashboard'));
 }
 public function logout(Request $request) {
     Auth::logout();
     $request->session()->invalidate();
     $request->session()->regenerateToken();
     return redirect()->route('manage.login');
 }
}
