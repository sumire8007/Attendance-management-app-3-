<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationRequest;
use App\Models\AttendanceRestApplication;
use Carbon\Carbon;
use Illuminate\Auth\Events\Login;
use Illuminate\Console\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use App\Models\AttendanceApplication;
use App\Models\RestApplication;
use App\Models\AttendanceRest;
use Vtiful\Kernel\Format;

class StaffController extends Controller
{
    //勤怠の表示
    public function attendanceView(){
        $date = Carbon::now()->locale('ja');
        $userId = Auth::user()->id;
        $attendance = Attendance::where('user_id', $userId)
        ->whereDate('attendance_date', $date->format('Y-m-d'))
        ->first();
        $rest = Rest::where('user_id', $userId)
        ->whereDate('rest_date', $date->format('Y-m-d'))
        ->whereNull('rest_out_at')
        ->first();
        return view('staff.attendance',compact('date','attendance','rest'));
    }
    //勤怠リストの表示
    public function attendanceListView($year = null, $month = null){
        $userId = Auth::user()->id;
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
        $rests = Rest::where('user_id', $userId)
            ->whereBetween('rest_date', [$startOfMonth, $endOfMonth])
            ->get();
        $restTotals = $rests->groupBy(function ($rest) {
            return Carbon::parse($rest->rest_date)->format('Y-m-d');
        })->map(function ($restsForDay) {
            return $restsForDay->sum('rest_total');
        });

        $attendanceDate = $dates->map(function($day) use ($attendances, $restTotals) {
            $record = $attendances[$day->format('Y-m-d')] ?? null;
            $dateKey = $day->format('Y-m-d');
            $in = $record && $record->clock_in_at ? Carbon::parse($record->clock_in_at)->format('H:i') : null;
            $out = $record && $record->clock_out_at ? Carbon::parse($record->clock_out_at)->format('H:i') : null;
            $restMinutes = $restTotals[$dateKey] ?? 0;
            $restTotal = sprintf('%02d:%02d', intdiv($restMinutes, 60), $restMinutes % 60);
            $attendanceTotal = $record && $record->attendance_total ? 
            ($record->attendance_total) - $restMinutes : null;
            $total = sprintf('%02d:%02d', intdiv($attendanceTotal, 60), $attendanceTotal % 60);
            $dateId = $record ? $record->id : null;
            return [
                'date' => $day,
                'clock_in_at' => $in,
                'clock_out_at' => $out,
                'rest_total' => $restTotal,
                'work' => $total ,
                'id' => $dateId,
            ];
        });
        return view('staff.attendance_list',[
            'attendanceDate' => $attendanceDate,
            'currentDate' => $date,
            'prevMonth' => $date->copy()->subMonth(),
            'nextMonth' => $date->copy()->addMonth(),
        ]);
    }
    //勤怠詳細表示
    public function attendanceDetail($attendanceId = null ,$applicationId = null)
    {
        //修正画面用
        $attendanceDates = Attendance::where('id', $attendanceId)->with('user')->first();
        $date = Carbon::parse($attendanceDates->attendance_date);
        $in = $attendanceDates->clock_in_at ? Carbon::parse($attendanceDates->clock_in_at)->format('H:i') : '-- : --';
        $out = $attendanceDates->clock_out_at ? Carbon::parse($attendanceDates->clock_out_at)->format('H:i') : '-- : --';
        $restDates = AttendanceRest::where('attendance_id', $attendanceId)
            ->with('rest')
            ->get();
        $approval = null;
        $waitApproval = null;
        //承認待ちのデータがあるのか($waitApproval)、全て承認済みか($approval)
        $attendanceApplicationDates = AttendanceApplication::where('attendance_id', $attendanceId)->get();
        foreach ($attendanceApplicationDates as $attendanceApplicationDate) {
            $waitApproval = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDate->id)
                ->whereNull('approval_at')
                ->first();

            $approval = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDate->id)
                ->whereNotNull('approval_at')
                ->first();
        }
        //承認待ちのデータがある時
        if (!empty($waitApproval)) {
            $attendanceApplicationDate = AttendanceRestApplication::where('attendance_application_id', $waitApproval->attendance_application_id)
                ->with('attendanceApplication', 'user')
                ->first();

            $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $waitApproval->attendance_application_id)
                ->with('restApplication')
                ->get();
            return view('staff.attendance_detail', compact('attendanceId','applicationId','attendanceDates', 'date', 'in', 'out', 'restDates', 'waitApproval', 'approval', 'attendanceApplicationDate', 'restApplicationDates'));
        }
        if ($applicationId) {
            //1回以上申請したことがあり、再申請が可能
            if (!empty($approval)) {
                $attendanceApplicationDate = AttendanceRestApplication::where('id', $applicationId)
                    ->with('attendanceApplication', 'user')
                    ->first();
                $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDate->attendance_application_id)
                    ->with('restApplication')
                    ->get();
                return view('staff.attendance_detail', compact('attendanceId','applicationId', 'attendanceDates', 'date', 'in', 'out', 'restDates', 'approval', 'attendanceApplicationDate', 'restApplicationDates', 'waitApproval'));
            }
        }
        return view('staff.attendance_detail', compact('attendanceId', 'applicationId','waitApproval', 'attendanceDates', 'date', 'in', 'out', 'restDates'));
    }
    // 勤怠を修正
    public function application(ApplicationRequest $request)
    {
        $attendanceDate = Attendance::where('id',$request->attendance_id)->value('attendance_date');
        $attendanceApplication = AttendanceApplication::create([
            'attendance_id' => $request->attendance_id,
            'attendance_change_date' => Carbon::parse($attendanceDate)->format('Y-m-d'),
            'clock_in_change_at' => $request->clock_in_change_at,
            'clock_out_change_at' => $request->clock_out_change_at,
            'remark_change' => $request->remark_change,
            'attendance_change_total' => Carbon::parse($request->clock_out_change_at)->diffInMinutes(Carbon::parse($request->clock_in_change_at)),
        ]);

        $restIds = $request->input('rest_id');
        $restIns = $request->input('rest_in_at');
        $restOuts = $request->input('rest_out_at');

        $restApplications = [];
        for ($i = 0; $i < count($restIns); $i++) {
            // 入力が空の行はスキップ（例：新規行が空欄のまま送信された場合）
            if (empty($restIns[$i]) || empty($restOuts[$i])) {
                continue;
            }
            $restDate = Rest::where('id',$restIds[$i])->value('rest_date');
            $restApplications[] = RestApplication::create([
                'rest_id' => $restIds[$i]?? null,
                'rest_change_date' => Carbon::parse($attendanceDate)->format('Y-m-d'),
                'rest_in_change_at' => $restIns[$i],
                'rest_out_change_at' => $restOuts[$i],
                'rest_change_total' => Carbon::parse($restOuts[$i])->diffInMinutes(Carbon::parse($restIns[$i])),
            ]);
        }
        if(count($restApplications) > 0){
            foreach ($restApplications as $restApplication) {
                AttendanceRestApplication::create([
                    'user_id' => Auth::user()->id,
                    'attendance_application_id' => $attendanceApplication->id,
                    'rest_application_id' => $restApplication->id,
                ]);
            }
        }else{
            AttendanceRestApplication::create([
                'user_id' => Auth::user()->id,
                'attendance_application_id' => $attendanceApplication->id,
                'rest_application_id' => null,
            ]);
        }
        return redirect('/attendance/list');
    }
    //申請一覧の表示
    public function requestListView(){
        $user = Auth::user();
        //承認待ちのデータ
        $waitingApprovals = AttendanceRestApplication::where('user_id',$user->id)
        ->whereNull('approval_at')
        ->with('attendanceApplication','restApplication','user')
        ->get()
        ->unique('attendance_application_id');
        //承認済みのデータ
        $approvals = AttendanceRestApplication::where('user_id',$user->id)
        ->whereNotNull('approval_at')
        ->with('attendanceApplication', 'restApplication', 'user')
        ->get()
        ->unique('attendance_application_id');
        return view('staff.request' ,compact('waitingApprovals','approvals'));
    }
    //出勤
    public function AddClockIn(){
        $date = now();
        $userId = Auth::user()->id;
        $attendance = Attendance::create([
            'user_id' => $userId,
            'attendance_date' => $date->format('Y-m-d'),
            'clock_in_at' => $date->format('H:i:s'),
        ]);
        return redirect('/attendance')->with('message','出勤を受付ました!');
    }
    // 退勤
    public function AddClockOut(){
        $date = Carbon::now();
        $userId = Auth::user()->id;
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('attendance_date', $date->format('Y-m-d'))
            ->first();
        $clockIn = Carbon::parse($attendance->clock_in_at);
        $clockOut = Carbon::now();
        $attendanceTotal = $clockOut->diffInMinutes($clockIn);
        Attendance::where('user_id',$userId)
        ->whereDate('attendance_date',$date->format('Y-m-d'))
        ->first()
        ->update([
            'clock_out_at' => $clockOut->format('H:i:s'),
            'attendance_total' => $attendanceTotal,
        ]);
        return redirect('/attendance');
    }
    //休憩入り
    public function AddRestIn(){
        $date = Carbon::now();
        $userId = Auth::user()->id;
        $attendance = Attendance::where([
            ['user_id', '=', $userId],
            ['attendance_date', '=', $date->format('Y-m-d')],
        ])->first();
        $rest = Rest::create([
            'user_id' => $userId,
            'rest_date' => $date->format('Y-m-d'),
            'rest_in_at' => $date->format('H:i:s'),
        ]);
        // 中間テーブルに保存
        AttendanceRest::create([
            'attendance_id' => $attendance->id,
            'rest_id' => $rest->id,
        ]);
        return redirect('/attendance')->with('message', '休憩入りを受付ました!');
    }
    //休憩戻り
    public function AddRestOut(){
        $date = Carbon::now();
        $userId = Auth::user()->id;
        $rest = Rest::where('user_id', $userId)
            ->whereDate('rest_date', $date)
            ->whereNull('rest_out_at')
            ->first();
        $restIn = Carbon::parse($rest->rest_in_at);
        $restOut = Carbon::now();
        $restTotal = $restOut->diffInMinutes($restIn);
        Rest::where('user_id',$userId)
        ->whereDate('rest_date',$date)
        ->whereNull('rest_out_at')
        ->first()
        ->update([
            'rest_out_at' => $date->format('H:i:s'),
            'rest_total' => $restTotal,
        ]);
        return redirect('/attendance')->with('message', '休憩戻りを受付ました!');
    }

}
