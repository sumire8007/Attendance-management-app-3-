@extends('layouts.staff_default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_content">
            @if(session('message'))
                <div>{{ session('message') }}</div>
            @endif
            @if(empty($attendance))
                <div class="attendance_status">勤務外</div>
            @elseif(isset($attendance->clock_in_at) && empty($attendance->clock_out_at) && empty($rest))
                <div class="attendance_status">出勤中</div>
            @elseif(isset($rest->rest_in_at) && empty($rest->rest_out_at))
                <div class="attendance_status">休憩中</div>
            @elseif(isset($attendance->clock_out_at))
                <div class="attendance_status">退勤済み</div>
            @endif

            <div class="attendance_day"> {{ $date->year . '年' . $date->month . '月' . $date->day . '日' }}</div>
            <div class="attendance_time">{{ $date->format('H:i') }}</div>

            @if(empty($attendance))
            <form action="/attendance" method="post">
                @csrf
                <button class="attendance_button">出勤</button>
            </form>
            @elseif(isset($attendance->clock_in_at) && empty($attendance->clock_out_at) && empty($rest))
                <div class="working_button_group">
                    <form action="/attendance/clockout" method="post">
                        @csrf
                            <button class="attendance_button">退勤</button>
                    </form>
                    <form action="/attendance/restin" method="post">
                        @csrf
                            <button class="rest_button">休憩入</button>
                    </form>
                </div>
            @elseif(isset($rest->rest_in_at) && empty($rest->rest_out_at))
                <form action="/attendance/restout" method="post">
                    @csrf
                        <button class="rest_button">休憩戻</button>
                </form>
            @elseif(isset($attendance->clock_out_at))
                <p class="clock_out_message">お疲れ様でした。</p>
            @endif
        </div>
    </div>
@endsection