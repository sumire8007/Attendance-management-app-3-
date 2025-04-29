@extends('layouts.staff_default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_content">
            <div class="attendance_status">勤務外</div>
            <div class="attendance_day"> {{ $dt->year.'年'.$dt->month.'月'.$dt->day.'日' }}</div>
            <div class="attendance_time">{{ $dt->format('H:i') }}</div>
            <form action="" method="post">
                @csrf
                <button>出勤</button>
            </form>
        </div>
    </div>
@endsection