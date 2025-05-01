@extends('layouts.staff_default')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_title">
            <h2>勤怠一覧</h2>
        </div>
        <div class="attendance_day">
            <span><a href="{{ url('/attendance/list', ['year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">←前月</a></span>
            <p>📅{{ $currentDate->format('Y/m') }}</p>
            <span><a href="{{ url('/attendance/list', ['year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">翌月→</a></span>
        </div>

        <div class="attendance_table">
            <table>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>

                @foreach($attendanceDate as $date)
                    <tr>
                        <td>{{ $date['date']->format('m/d(D)') }}</td>
                        <td>{{ $date['clock_in_at'] ?? '-' }}</td>
                        <td>{{ $date['clock_out_at'] ?? '-' }}</td>
                        <td>{{ $date['rest'] ?? '-' }}</td>
                        <td>{{ $date['work'] ?? '-' }}</td>
                    </tr>
                @endforeach
                <!-- <tr>
                    <td>06/01(木)</td>
                    <td>9:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <form action="" method="">
                    <td>
                        <a href="/attendance/id">詳細</a>
                    </td>
                    </form>
                </tr>
                <tr>
                    <td>06/02(金)</td>
                    <td>9:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
                    <form action="">
                    <td>
                        <a href="/attendance/id">詳細</a>
                    </td>

                    </form>
                </tr> -->
            </table>
        </div>
    </div>

@endsection