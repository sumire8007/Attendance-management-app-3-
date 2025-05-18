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
            <div class="attendance_sub-title">
                <img class="calendar-icon" src="{{ asset('img/calendar.jpeg') }}" alt="カレンダー">
                <p>{{ $currentDate->format('Y/m') }}</p>
            </div>
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
                        <td>{{ $date['date']->translatedFormat('m/d(D)') }}</td>
                        <td>{{ $date['clock_in_at'] ?? '-' }}</td>
                        <td>{{ $date['clock_out_at'] ?? '-' }}</td>
                        <td>{{ $date['rest_total'] ?? '-' }}</td>
                        <td>{{ $date['work'] ?? '-' }}</td>
                        <td>
                            @if($date['id'])
                            <a href={{ url('/attendance', ['attendanceId' => $date['id']] ) }}>詳細</a>
                            @else
                            -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

@endsection