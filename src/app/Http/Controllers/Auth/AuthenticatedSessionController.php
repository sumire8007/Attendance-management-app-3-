<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController as FortifyAuthenticatedSessionController;
use Illuminate\Support\Facades\Gate;
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
use App\Models\User;
use App\Http\Requests\LoginRequest;


class AuthenticatedSessionController extends FortifyAuthenticatedSessionController
{
    // ログイン処理(formRequestでバリデーション)
    public function store(Request $request){
        $credentials = $request->only('email', 'password');
        $user_role = User::where('email', $request->email)->pluck('role');
        // dd($request);
        if(Auth::attempt($credentials)){
            $user = Auth::user();
            if ($request->is('admin/login')) {
                if (Gate::allows('admin-higher', $user)) {
                    $request->session()->regenerate();
                    return redirect('/admin/attendance/list');
                }
            } elseif ($request->is('login')) {
                if (Gate::allows('user-higher', $user)) {
                    $request->session()->regenerate();
                    return redirect('/attendance');
                }
            }

        }
    }


    // ログアウト処理
    public function logout(Request $request){
        if($request->is('/admin/logout')){
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/admin/login');
        }else{
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect('/login');
        }
    }
}
