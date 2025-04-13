@extends('layouts.staff_default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
    <div class="attendance_group">
        <div>
            <div>勤務外</div>
            <div>2023年6月1日(木)</div>
            <div>現在の時刻</div>
            <form action="" method="post">
                @csrf
                <button>出勤</button>←退勤、休憩入り(戻り)に変化
            </form>
        </div>
    </div>
@endsection