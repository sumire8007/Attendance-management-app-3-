@extends('layouts.admin_default')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_title">
            <h2>{{ $date->year . '年' . $date->month . '月' . $date->day . '日' }}の勤怠</h2>
        </div>
        <div class="attendance_day">
            <span>
                <a href="{{ url('admin/attendance/list', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'day' => $prevMonth->day]) }}">←前日</a>
            </span>
            <div class="attendance_sub-title">
                <img class="calendar-icon" src="{{ asset('img/calendar.jpeg') }}" alt="カレンダー">
                <p>{{ $date->format('Y/m/d') }}</p>
            </div>
            <span>
                <a href="{{ url('admin/attendance/list', ['year' => $nextMonth->year, 'month' => $nextMonth->month , 'day' => $nextMonth->day]) }}">翌日→</a>
            </span>
        </div>

        <div class="attendance_table">
            <table>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>

                @foreach($attendanceDates as $attendanceDate)
                    <tr>
                        <td>{{ $attendanceDate['name'] ?? '-' }}</td>
                        <td>{{ $attendanceDate['clock_in_at'] }}</td>
                        <td>{{ $attendanceDate['clock_out_at'] }}</td>
                        <td>{{ $attendanceDate['rest_total'] }}</td>
                        <td>{{ $attendanceDate['total'] }}</td>
                        <td><a href="/attendance/{{ $attendanceDate['id'] }}">詳細</a></td>
                    </tr>
                @endforeach

            </table>
        </div>
    </div>

@endsection