<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceApplication;
use App\Models\RestApplication;
use Vtiful\Kernel\Format;

class StaffController extends Controller
{
    //勤怠の表示
    public function attendanceView(){
        $dt = Carbon::now();
        return view('staff.attendance',compact('dt'));
    }
    //勤怠リストの表示
    public function attendanceListView($year = null, $month = null){
        $userId = auth()->id();
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, 1);
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $dates = collect();
        foreach($startOfMonth->toPeriod($endOfMonth) as $day){
            $dates->push($day->copy());
        }
        $attendances = Attendance::where('user_id', $userId)
        ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
        ->get()
        ->keyBy('attendance_date');

        $attendanceDate = $dates->map(function($day) use ($attendances) {
            $record = $attendances[$day->format('Y-m-d')] ?? null;
            $in = $record ? $record->clock_in_at : null;
            $out = $record ? $record->clock_out_at : null;
            $rest = $record ? '01:00' : null;
            $work = null;
            if($in && $out){
                $start = Carbon::parse($in);
                $end = Carbon::parse($out);
                $workDuration = $end->diffInMinutes($start) - 60;
                $work =sprintf('%d:%02d',floor($workDuration/60),$workDuration % 60);
            }
            return [
                'date' => $day,
                'clock_in_at' => $in,
                'clock_out_at' => $out,
                'rest' => $rest,
                'work' => $work,
                // 'id' => $record->id,
            ];
        });

        return view('staff.attendance_list',[
            'attendanceDate' => $attendanceDate,
            'currentDate' => $date,
            'prevMonth' => $date->copy()->subMonth(),
            'nextMonth' => $date->copy()->addMonth(),
        ]);
    }
    //申請一覧の表示
    public function requestListView(){
        return view('staff.request');
    }
    //勤怠詳細(申請入力)
    public function attendanceDetail(){
        return view('staff.attendance_detail');
    }

}
