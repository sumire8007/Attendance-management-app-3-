@extends('layouts.staff_default')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_detail.css') }}">
@endsection

@section('content')

    <div class="attendance_group">
        <div class="attendance_title">
            <h2>勤怠詳細</h2>
        </div>
        <form action="" method="">
            <div class="attendance_table">
                <table>
                    <tr>
                        <th>名前</th>
                        <td>{{ $attendanceDate->user->name }}</td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>{{ $attendanceDate->attendance_date }}</td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <input type="text"  name="clock_in_at" value="{{ \Carbon\Carbon::parse($attendanceDate->clock_in_at)->format('H:i') }}">
                            <p>~</p>
                            <input type="text" name="clock_out_at" value="{{ \Carbon\Carbon::parse($attendanceDate->clock_out_at)->format('H:i') }}">
                        </td>
                    </tr>
                    @foreach($restDates as $restDate)
                        <tr>
                            <th>休憩</th>
                            <td>
                                <input type="text" name="rest_in_at" value="{{ \Carbon\Carbon::parse($restDate->rest->rest_in_at)->format('H:i') }}">
                                <p>~</p>
                                <input type="text" name="rest_out_in" value="{{ \Carbon\Carbon::parse($restDate->rest->rest_out_at)->format('H:i') }}">
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <th>備考</th>
                        <td class="textarea">
                            <textarea name="remark"  cols="20" rows="3">{{ $attendanceDate->remark }}</textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="attendance_correction_button">
                <button>修正</button>
            </div>
        </form>
    </div>
@endsection