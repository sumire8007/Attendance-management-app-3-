@extends('layouts.staff_default')

@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}" />
@endsection

@section('content')
<div>
    <div>勤務外</div>
    <div>2023年6月1日(木)</div>
    <div>現在の時刻</div>
</div>
    <button>出勤</button>

@endsection