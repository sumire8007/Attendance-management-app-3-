<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Illuminate\Support\Facades\Hash;


class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
    // ログイン処理
    public function store(Request $request){
        if($request->is('admin/login')){
            if(Auth::guard('admin')->attempt($request->only('email','password'))){
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
    // ログアウト処理
    public function logout(Request $request){
        if($request->is('admin/logout')){
            Auth::guard('admin')->logout();
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
