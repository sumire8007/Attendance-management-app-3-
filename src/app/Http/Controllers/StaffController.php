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
        // dd($rest);
        return view('staff.attendance',compact('date','attendance','rest'));
    }
    //勤怠リストの表示
    public function attendanceListView($year = null, $month = null){
        $userId = auth()->id();
        // パラメーターのyearがあったらそれを使う、無ければnow　※monthも同じく　例）$date = 2025-05-01
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month, 1);
        // 指定月の開始日と終了日を取得
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        // 月の日付を1日ずつリストに入れる　　$dates = collect();は$dateの中は配列になるような箱を用意
        $dates = collect();
        foreach($startOfMonth->toPeriod($endOfMonth) as $day){
            $dates->push($day->copy());  //push()は追加、copy()は$dayを上書きしないように
        }
        // ログインしているユーザーの勤怠データのうち、パラメーターで指定された年月の1日〜30日までを$attendancesに格納
        $attendances = Attendance::where('user_id', $userId)
        ->whereBetween('attendance_date', [$startOfMonth, $endOfMonth])
        ->get()
        ->keyBy('attendance_date');
        // 日付ごとにデータを整形　map()はlaravel collectionの繰り返しメソッド
        $attendanceDate = $dates->map(function($day) use ($attendances) {
            // $attendancesの中からその日の日付を探す、無ければエラーを返すのではなくnullを格納
            $record = $attendances[$day->format('Y-m-d')] ?? null;
            // $record中がnull,その日のデータが無いときnull,あったらその日のデータ（clock_in_at,clock_out_atを格納）
            $in = $record? $record->clock_in_at : null;
            $out = $record? $record->clock_out_at : null;
            // 三項演算子、$recordがあればTRUE＝1：00、FALSE＝null
            $rest = $record ? '01:00' : null;
            $dateId = $record ? $record->id : null;
            // 出勤と退勤があれば、勤務時間を計算して、H:i形式で表示
            $work = null;
            if($in && $out){
                $start = Carbon::parse($in);
                $end = Carbon::parse($out);
                $workDuration = $end->diffInMinutes($start) - 60;
                $work =sprintf('%d:%02d',floor($workDuration/60),$workDuration % 60);
            }
            // 日ごとの表示用データを配列で返す
            return [
                'date' => $day,
                'clock_in_at' => $in,
                'clock_out_at' => $out,
                'rest' => $rest,
                'work' => $work,
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

    //申請一覧の表示
    public function requestListView(){
        return view('staff.request');
    }
    //勤怠詳細(申請入力)
    public function attendanceDetail(){
        return view('staff.attendance_detail');
    }


    //出勤
    public function AddClockIn(){
        $date = now();
        $userId = Auth::user()->id;
        // dd($userId);
        $attendance = Attendance::create([
            'user_id' => $userId,
            'attendance_date' => $date->format('Y-m-d'),
            'clock_in_at' => $date->format('H:i:s'),
        ]);
        // dd($attendance);
        return redirect('/attendance')->with('message','出勤を受付ました！');
    }
    // 退勤
    public function AddClockOut(){
        $date = Carbon::now();
        $userId = Auth::user()->id;
        $attendance = Attendance::where('user_id',$userId)
        ->whereDate('attendance_date',$date->format('Y-m-d'))
        ->first()
        ->update(['clock_out_at'=> $date->format('H:i:s')]);
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
        return redirect('/attendance')->with('message', '休憩入りを受付ました！');
    }
    //休憩戻り
    public function AddRestOut(){
        $date = Carbon::now();
        $userId = Auth::user()->id;
        $rest = Rest::where('user_id',$userId)
        ->whereDate('rest_date',$date)
        ->whereNull('rest_out_at')
        ->first()
        ->update(['rest_out_at' => $date->format('H:i:s')]);
        return redirect('/attendance')->with('message', '休憩戻りを受付ました。');
    }
}
