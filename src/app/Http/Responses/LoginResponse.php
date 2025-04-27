<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
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
