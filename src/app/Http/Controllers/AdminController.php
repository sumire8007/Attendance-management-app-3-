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

        // return view('admin.admin_staff_attendance_list' , compact('user','date'));
    }
    //申請一覧表示
    public function requestList()
    {
        //承認待ちのデータ
        $waitingApprovals = AttendanceRestApplication::whereNull('approval_at')
            ->with('attendanceApplication', 'restApplication', 'user')
            ->get();
        //承認済みのデータ
        $approvals = AttendanceRestApplication::whereNotNull('approval_at')
            ->with('attendanceApplication', 'restApplication', 'user')
            ->get();
        return view('admin.admin_request_list', compact('waitingApprovals', 'approvals'));
    }
    //修正承認画面表示
    public function approval($id =  null)
    {
        $attendanceApplicationDateId = AttendanceApplication::where('attendance_id', $id)->first();
            $attendanceApplicationDate = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDateId->id)
                ->with('attendanceApplication', 'user')
                ->first();

            $restApplicationDates = AttendanceRestApplication::where('attendance_application_id', $attendanceApplicationDateId->id)
                ->with('restApplication')
                ->get();
            return view('admin.admin_approval', compact( 'attendanceApplicationDateId', 'attendanceApplicationDate', 'restApplicationDates'));
    }

}
