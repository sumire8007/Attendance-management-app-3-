@extends('layouts.staff_default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_content">
            <div class="attendance_status">勤務外</div>
            <div class="attendance_day">2023年6月1日(木)</div>
            <div class="attendance_time">8:00</div>
            <form action="" method="post">
                @csrf
                <button>出勤</button>
            </form>
        </div>
    </div>
@endsection