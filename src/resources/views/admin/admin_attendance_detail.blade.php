@extends('layouts.admin_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')
        <div class="attendance_group">
            <div class="attendance_title">
                <h2>勤怠詳細</h2>
            </div>
            @if(session('message'))
                <div class="action-message">{{ session('message') }}</div>
            @endif

            <form action="/admin/attendance/application" method="post">
                @csrf
                <input type="hidden" name="attendance_id" value="{{ $attendanceDates->id }}">
                <input type="hidden" name="user_id" value="{{ $attendanceDates->user->id }}">
                <div class="attendance_table">
                    <table>
                        <tr>
                            <th>名前</th>
                            <td>{{ $attendanceDates->user->name }}</td>
                        </tr>
                        <tr>
                            <th>日付</th>
                            <td>{{ $date->year . '年' . $date->month . '月' . $date->day . '日' }}</td>
                            <td></td>
                        </tr>
                        <tr>
                            <th>出勤・退勤</th>
                            <td>
                                @error("clock_in_change_at")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <input type="time"  name="clock_in_change_at" value="{{ $in }}">
                                <p>~</p>
                                <input type="time" name="clock_out_change_at" value="{{ $out }}">
                            </td>
                        </tr>
                        @foreach($restDates as $index => $restDate)
                            <input type="hidden" name="rest_id[]" value="{{ $restDate->rest->id }}">
                            <tr>
                                <th>休憩{{ $index + 1 }}</th>
                                <td>
                                    @error("rest_in_at.$index")
                                        <span class="error-message">{{ $message }}</span>
                                    @enderror

                                    <input type="time" name="rest_in_at[]" value="{{ \Carbon\Carbon::parse($restDate->rest->rest_in_at)->format('H:i') }}">
                                    <p>~</p>
                                    <input type="time" name="rest_out_at[]" value="{{ $restDate->rest->rest_out_at ? \Carbon\Carbon::parse($restDate->rest->rest_out_at)->format('H:i') : ''}}">
                                </td>
                            </tr>
                        @endforeach
                        @php
    $restDateIndex = count($restDates) + 1
                        @endphp
                        <tr>
                            <th>休憩{{ $restDateIndex }}</th>
                            <td>
                                @error("rest_in_at.$restDateIndex")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <input type="time" name="rest_in_at[]" value="">
                                <p>~</p>
                                <input type="time" name="rest_out_at[]" value="">
                                <input type="hidden" name="rest_id[]" value="">
                            </td>
                        </tr>
                        <tr>
                            <th>備考</th>
                            <td class="textarea">
                                @error("remark_change")
                                    <span class="error-message">{{ $message }}</span>
                                @enderror
                                <textarea name="remark_change"  cols="20" rows="3">{{ $attendanceDates->remark }}</textarea>
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