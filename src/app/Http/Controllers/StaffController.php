<?php

namespace App\Http\Controllers;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    //
    public function index(){
        return view('auth.manager_login');
    }
}
