@extends('layouts.admin_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_staff_attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_title">
            <h2>{{ $user->name }}さんの勤怠</h2>
        </div>
        <div class="attendance_day">
            <span>
                <p><a
                        href="{{ url('/admin/attendance/staff', ['id' => $user->id, 'year' => $prevMonth->year, 'month' => $prevMonth->month]) }}">←前月</a>
                </p>
            </span>
            <p>
            <div class="attendance_sub-title">
                <img class="calendar-icon" src="{{ asset('img/calendar.jpeg') }}" alt="カレンダー">
                <p>{{ $date->format('Y/m') }}</p>
            </div>
            </p>
            <span>
                <p><a
                        href="{{ url('/admin/attendance/staff', ['id' => $user->id, 'year' => $nextMonth->year, 'month' => $nextMonth->month]) }}">翌月→</a>
                </p>
            </span>
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
                                <a href="/admin/attendance/{{ $date['id'] }}">詳細</a>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach

            </table>
        </div>
        <div class="csv_button">
            <form action="/export" method="post">
                @csrf
                <button>CSV出力</button>
            </form>
        </div>
    </div>

@endsection