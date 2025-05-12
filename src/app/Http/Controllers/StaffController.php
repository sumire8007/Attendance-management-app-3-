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
        $date = Carbon::now();
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
        // パラメーターのyearがあったらそれを使う、無ければnow　※monthも同じく　例）$date = 2025-05-01
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, 1);
        // 指定月の開始日と終了日を取得
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        // 月の日付を1日ずつリストに入れる　　$dates = collect();は$dateの中は配列になるような箱を用意
        $dates = collect();
        foreach($startOfMonth->toPeriod($endOfMonth) as $day){
            $dates->push($day->copy());
        }
        // ログインしているユーザーの勤怠データのうち、パラメーターで指定された年月の1日〜30日までを$attendancesに格納
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

        // 日付ごとにデータを整形　map()はlaravel collectionの繰り返しメソッド
        $attendanceDate = $dates->map(function($day) use ($attendances, $restTotals) {
            // $attendancesの中からその日の日付を探す、無ければエラーを返すのではなくnullを格納
            $record = $attendances[$day->format('Y-m-d')] ?? null;
            $dateKey = $day->format('Y-m-d');
            // $record中がnull,その日のデータが無いときnull,あったらその日のデータ（clock_in_at,clock_out_atを格納）
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
            'attendanceDate' => $attendanceDate,  //表示用の勤怠情報（日別）
            'currentDate' => $date,  //今表示している月
            'prevMonth' => $date->copy()->subMonth(), //今表示している月の1ヶ月前
            'nextMonth' => $date->copy()->addMonth(),//今表示している月の1ヶ月後
        ]);
    }
    //勤怠詳細表示
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
        if(!empty($attendanceApplicationDateId)){
            $attendanceApplicationDate = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDateId->id)
                ->with('attendanceApplication', 'user')
                ->first();

            $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDateId->id)
                ->with('restApplication')
                ->get();
            return view('staff.attendance_detail', compact('attendanceDates', 'date', 'in', 'out', 'restDates', 'attendanceApplicationDateId','attendanceApplicationDate', 'restApplicationDates'));
        }
        return view('staff.attendance_detail',compact('attendanceDates','date','in','out','restDates', 'attendanceApplicationDateId'));
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
            dd($restDate);
            $restApplications[] = RestApplication::create([
                'rest_id' => $restIds[$i]?? null,
                'rest_change_date' => Carbon::parse($restDate)->format('Y-m-d'),
                'rest_in_change_at' => $restIns[$i],
                'rest_out_change_at' => $restOuts[$i],
                'rest_change_total' => Carbon::parse($restOuts[$i])->diffInMinutes(Carbon::parse($restIns[$i])),
            ]);
        }
        foreach($restApplications as $restApplication){
            AttendanceRestApplication::create([
                'user_id' => Auth::user()->id,
                'attendance_application_id' => $attendanceApplication->id,
                'rest_application_id' => $restApplication->id,
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
        // 中間テーブルにも保存しておく
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
