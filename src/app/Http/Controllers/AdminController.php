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
        $attendances = Attendance::whereDate('attendance_date', $date)->with('user')->get();

        $rests = collect();
        foreach($attendances as $attendance){
            $restDates = AttendanceRest::where('attendance_id', $attendance->id)->with('rest')->get();
            $rests = $rests->merge($restDates);
        }
        $restTotals = $rests->groupBy('attendance_id')->map(function ($group) {
            return $group->sum(function ($items) {
                return optional($items->rest)->rest_total ?? null;
            });
        });
        $attendanceDates = [];

        foreach($attendances as $attendance){
            $restSum = $restTotals[$attendance->id] ?? 0;
            $work = $attendance->attendance_total !== null ? $attendance->attendance_total - $restSum : null;

            $attendanceDates[] = [
                'name' => $attendance->user->name,
                'clock_in_at' => $attendance->clock_in_at ? Carbon::parse($attendance->clock_in_at)->format('H:i') : '-',
                'clock_out_at' => $attendance->clock_out_at ? Carbon::parse($attendance->clock_out_at)->format('H:i') : '-' ,
                'rest_total' => sprintf('%02d:%02d', intdiv($restSum, 60), $restSum % 60),
                'total' => sprintf('%02d:%02d', intdiv($work, 60), $work % 60),
                'id' => $attendance->id,
            ];
        }
        $prevMonth = $date->copy()->subDay();
        $nextMonth = $date->copy()->addDay();
        return view('admin.admin_attendance_list' ,compact('date','prevMonth','nextMonth','attendanceDates'));
    }
    //勤怠詳細
    public function attendanceDetail($id)
    {
        //修正画面用
        $attendanceDates = Attendance::where('id', $id)->with('user')->first();
        $date = Carbon::parse($attendanceDates->attendance_date);
        $in = $attendanceDates->clock_in_at ? Carbon::parse($attendanceDates->clock_in_at)->format('H:i') : '-- : --';
        $out = $attendanceDates->clock_out_at ? Carbon::parse($attendanceDates->clock_out_at)->format('H:i') : '-- : --';
        $restDates = AttendanceRest::where('attendance_id', $id)
            ->with('rest')
            ->get();
        //承認待ち用
        $attendanceApplicationDateId = AttendanceApplication::where('attendance_id', $id)->first();
        if (!empty($attendanceApplicationDateId)) {
            $attendanceApplicationDate = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDateId->id)
                ->with('attendanceApplication', 'user')
                ->first();

            $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDateId->id)
                ->with('restApplication')
                ->get();
            return view('admin.admin_attendance_detail', compact('attendanceDates', 'date', 'in', 'out', 'restDates', 'attendanceApplicationDateId', 'attendanceApplicationDate', 'restApplicationDates'));
        }
        return view('admin.admin_attendance_detail', compact('attendanceDates', 'date', 'in', 'out', 'restDates', 'attendanceApplicationDateId'));
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
