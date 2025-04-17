<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;

class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
    public function store(Request $request){
        if($request->is('admin/login')){
            if(Auth::guard('manager')->attempt($request->only('email','password'))){
                $request->session()->regenerate();
                return redirect('/admin/attendance/list');
            }
        }else{
            if(Auth::guard('web')->attempt($request->only('email','password'))){
                $request->session()->regenerate();
                return redirect()->intended('/attendance');
            }
        }
    }
    public function logout(Request $request){
        if($request->is('admin/logout')){
            Auth::guard('manager')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/admin/login');
        }else{
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login');
        }
    }
}
