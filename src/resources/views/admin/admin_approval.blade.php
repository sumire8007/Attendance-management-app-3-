@extends('layouts.admin_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_approval.css') }}">
@endsection

@section('content')

    <div class="attendance_group">
        <div class="attendance_title">
            <h2>勤怠詳細</h2>
        </div>
            <div class="attendance_table">
                <table>
                    <tr>
                        <th>名前</th>
                        <td>
                            <p>{{ $attendanceApplicationDate->user->name }}</p>
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                        <p>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->attendance_change_date)->format('Y' . '年' . 'm' . '月' . 'd' . '日') }}</p>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <p>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->clock_in_change_at)->format('H:i') }}</p>
                            <p>~</p>
                            <p>{{ \Carbon\Carbon::parse($attendanceApplicationDate->attendanceApplication->clock_out_change_at)->format('H:i') }}</p>
                        </td>
                    </tr>
                    @foreach($restApplicationDates as $restApplication)
                        @php
                            $restApp = $restApplication->restApplication;
                        @endphp
                        @if($restApp)
                            <tr>
                                <th>休憩</th>
                                <td>
                                    <p>{{ \Carbon\Carbon::parse($restApp->rest_in_change_at)->format('H:i') }}</p>
                                    <p>~</p>
                                    <p>{{ \Carbon\Carbon::parse($restApp->rest_out_change_at)->format('H:i') }}</p>
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    <tr>
                        <th>休憩</th>
                        <td>
                            <p></p>
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td class="textarea">
                            <p>{{ $attendanceApplicationDate->attendanceApplication->remark_change }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            @if(empty($restApplicationDates->approval_at))
            <div class="attendance_button">
                <form action="" method="post">
                    @csrf
                    <button>承認</button>
                </form>
            </div>
            @elseif(isset($restApplicationDates->approval_at))
                <div class="approved_button">
                    承認済み
                </div>
            @endif
    </div>
@endsection