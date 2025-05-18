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
        $attendanceDates = Attendance::where('id', $id)->with('user')->first();
        $date = Carbon::parse($attendanceDates->attendance_date);
        $in = $attendanceDates->clock_in_at ? Carbon::parse($attendanceDates->clock_in_at)->format('H:i') : '-- : --';
        $out = $attendanceDates->clock_out_at ? Carbon::parse($attendanceDates->clock_out_at)->format('H:i') : '-- : --';
        $restDates = AttendanceRest::where('attendance_id', $id)
            ->with('rest')
            ->get();
        return view('admin.admin_attendance_detail', compact('attendanceDates', 'date', 'in', 'out', 'restDates'));
    }
    //勤怠修正
    public function application(ApplicationRequest $request)
    {
        $date = Attendance::where('id', $request->attendance_id)->value('attendance_date');
        Attendance::where('id',$request->attendance_id)
        ->update([
            'clock_in_at' => $request->clock_in_change_at,
            'clock_out_at' => $request->clock_out_change_at,
            'remark' => $request->remark_change,
            'attendance_total' => Carbon::parse($request->clock_out_change_at)->diffInMinutes(Carbon::parse($request->clock_in_change_at)),
        ]);

        //既存の休憩データがあったら
        $restIds = $request->input('rest_id');
        $restIns = $request->input('rest_in_at');
        $restOuts = $request->input('rest_out_at');

        for ($i = 0; $i < count($restIns); $i++) {
            // 入力が空の行はスキップ（例：新規行が空欄のまま送信された場合）
            if (empty($restIns[$i]) || empty($restOuts[$i])) {
                continue;
            }
            if(!empty($restIds[$i])){
                Rest::where('id', $restIds[$i])
                    ->update([
                        'rest_in_at' => $restIns[$i],
                        'rest_out_at' => $restOuts[$i],
                        'rest_total' => Carbon::parse($restOuts[$i])->diffInMinutes(Carbon::parse($restIns[$i])),
                    ]);
            }elseif(empty($restIds[$i])){
                $restDate = Rest::create([
                    'user_id' => $request->user_id,
                    'rest_date' => $date,
                    'rest_in_at' => $restIns[$i],
                    'rest_out_at' => $restOuts[$i],
                    'rest_total' => Carbon::parse($restOuts[$i])->diffInMinutes(Carbon::parse($restIns[$i])),
                ]);
                AttendanceRest::create([
                    'attendance_id' => $request->attendance_id,
                    'rest_id' => $restDate->id,
                ]);
            }
        }
        return redirect('/admin/attendance/'.$request->attendance_id)->with('message','勤怠を修正しました!');
    }
    //スタッフ一覧表示
    public function staffList()
    {
        $users = User::where('role',0)->get();
        return view('admin.admin_staff_list',compact('users'));
    }
    //スタッフ別勤怠月次一覧
    public function staffAttendanceList($id = null , $year = null ,$month = null)
    {
        $user = User::where('id', $id)->first();
        $userId = $user->id;
        $date = Carbon::createFromDate($year ?? now()->year, $month ?? now()->month);
        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        // 指定月の開始日と終了日を取得
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();
        // 月の日付を1日ずつリストに入れる　　$dates = collect();は$dateの中は配列になるような箱を用意
        $dates = collect();
        foreach ($startOfMonth->toPeriod($endOfMonth) as $day) {
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
        $attendanceDate = $dates->map(function ($day) use ($attendances, $restTotals) {
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
                'work' => $total,
                'id' => $dateId,
            ];
        });
        return view('admin.admin_staff_attendance_list', [
            'user' => $user,
            'date' => $date,
            'attendanceDate' => $attendanceDate,  //表示用の勤怠情報（日別）
            'currentDate' => $date,  //今表示している月
            'prevMonth' => $date->copy()->subMonth(), //今表示している月の1ヶ月前
            'nextMonth' => $date->copy()->addMonth(),//今表示している月の1ヶ月後
        ]);
    }
    //申請一覧表示
    public function requestList()
    {
        //承認待ちのデータ
        $waitingApprovals = AttendanceRestApplication::whereNull('approval_at')
            ->with('attendanceApplication', 'restApplication', 'user')
            ->get()
            ->unique('attendance_application_id');
        //承認済みのデータ
        $approvals = AttendanceRestApplication::whereNotNull('approval_at')
            ->with('attendanceApplication', 'restApplication', 'user')
            ->get()
            ->unique('attendance_application_id');
        return view('admin.admin_request_list', compact('waitingApprovals', 'approvals'));
    }
    //修正承認画面表示
    public function viewApproval($id =  null)
    {
        // 承認待ちのデータ（最新の申請データ）を探す
        // $attendanceApplicationDates = AttendanceApplication::where('attendance_id', $id)->get();
        // foreach($attendanceApplicationDates as $attendanceApplicationDate){
        //     $waitApproval = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDate->id)
        //         ->whereNull('approval_at')
        //         ->first();
        // }


        $applicationDate = AttendanceRestApplication::where('id', $id)
        ->with('attendanceApplication','user')
        ->first();
        // dd($applicationDate);

        $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $applicationDate->attendance_application_id)
            ->with('restApplication')
            ->get();
        // $attendanceApplicationDate = AttendanceApplication::where('id', $applicationDate->attendance_application_id)->first();

        // //承認待ちのデータがあったら
        // if(!empty($waitApproval)){
        //     $attendanceApplicationDate = AttendanceRestApplication::where('id', $waitApproval->id)
        //         ->with('attendanceApplication', 'user')
        //         ->first();
        //     $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $waitApproval->attendance_application_id)
        //         ->with('restApplication')
        //         ->get();
        // }
            return view('admin.admin_approval', compact( 'applicationDate', 'restApplicationDates'));
    }
    //承認機能
    public function approval(Request $request)
    {
        $date = Carbon::now();
        //承認日追加
        AttendanceRestApplication::where('attendance_application_id', $request->attendance_application_id)->update(['approval_at' => $date]);

        //元データを承認済みデータに上書き(出退勤)
        $attendanceDate = AttendanceApplication::where('id', $request->attendance_application_id)->first();
        Attendance::where('id',$attendanceDate->attendance_id)
        ->first()
        ->update([
            'clock_in_at' => $attendanceDate->clock_in_change_at,
            'clock_out_at' => $attendanceDate->clock_out_change_at,
            'remark' => $attendanceDate->remark_change,
            'attendance_total' => $attendanceDate->attendance_change_total,
        ]);

        // //元データを承認済みデータに上書き(既存の休憩データ)
        $applicationDates = AttendanceRestApplication::where('attendance_application_id', $request->attendance_application_id)
        ->get();
        foreach($applicationDates as $applicationDate){
            $restDate = RestApplication::where('id', $applicationDate->rest_application_id)
                ->whereNotNull('rest_id')
                ->first();
            if(isset($restDate)){
                Rest::where('id', $restDate->rest_id)
                    ->first()
                    ->update([
                    'rest_in_at' => $restDate->rest_in_change_at,
                    'rest_out_at' => $restDate->rest_out_change_at,
                    'rest_total' => $restDate->rest_change_total,
                    ]);
            }
        }

        //元データを承認済みデータに上書き(休憩の新規分)
        foreach($applicationDates as $applicationDate){
            $restNewDate = RestApplication::where('id', $applicationDate->rest_application_id)
                ->whereNull('rest_id')
                ->first();
            if(isset($restNewDate)){
                $rest = Rest::create([
                    'user_id' => $request->user_id,
                    'rest_date' => $restNewDate->rest_change_date,
                    'rest_in_at' => $restNewDate->rest_in_change_at,
                    'rest_out_at' => $restNewDate->rest_out_change_at,
                    'rest_total' => $restNewDate->rest_change_total,
                ]);
                AttendanceRest::create([
                    'attendance_id' => $attendanceDate->attendance_id,
                    'rest_id' => $rest->id,
                ]);
                RestApplication::where('id',$restNewDate->id)
                ->update(['rest_id' => $rest->id]);
            }
        }
        return redirect('/admin/stamp_correction_request/approve/'.$attendanceDate->id);
    }
}
