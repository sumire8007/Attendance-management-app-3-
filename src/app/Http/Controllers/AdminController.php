<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Requests\ApplicationRequest;
use App\Models\AttendanceRestApplication;
use Illuminate\Auth\Events\Login;
use Illuminate\Console\Application;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceApplication;
use App\Models\RestApplication;
use App\Models\AttendanceRest;
use Vtiful\Kernel\Format;


class AdminController extends Controller
{
    //当日の勤怠一覧画面表示
    public function attendanceList($year = null, $month = null, $day = null)
    {
        $now = Carbon::now();
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, $day ?? now()->day);
        $users = User::all();
        $attendances = Attendance::whereDate('attendance_date', $date)->get();
        $rests = Rest::whereDate('rest_date', $date)->get();
        $restTotals = $rests->groupBy(function ($restsForDay) {
            return $restsForDay->sum('rest_total');
        });

        $prevMonth = $date->copy()->subDay();
        $nextMonth = $date->copy()->addDay();
        // dd($rests);
        
        return view('admin.admin_attendance_list' ,compact('date','prevMonth','nextMonth'));
    }
    //勤怠詳細
    public function attendanceDetail()
    {
        return view('admin.admin_attendance_detail');
    }
    //スタッフ一覧表示
    public function staffList()
    {
        $users = User::where('role',0)->get();
        return view('admin.admin_staff_list',compact('users'));
    }
    //スタッフ別勤怠月次一覧
    public function staffAttendanceList()
    {
        return view('admin.admin_staff_attendance_list');
    }
    //申請一覧表示
    public function requestList()
    {
        return view('admin.admin_request_list');
    }
    //修正承認画面表示
    public function approval()
    {
        return view('admin.admin_approval');
    }

}
