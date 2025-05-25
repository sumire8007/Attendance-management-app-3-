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
                            <p>{{ $applicationDate->user->name }}</p>
                        </td>
                    </tr>
                    <tr>
                        <th>日付</th>
                        <td>
                        <p>{{ \Carbon\Carbon::parse($applicationDate->attendanceApplication->attendance_change_date)->format('Y' . '年' . 'n' . '月' . 'j' . '日') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <th>出勤・退勤</th>
                        <td>
                            <p>{{ \Carbon\Carbon::parse($applicationDate->attendanceApplication->clock_in_change_at)->format('H:i') }}</p>
                            <p>~</p>
                            <p>{{ \Carbon\Carbon::parse($applicationDate->attendanceApplication->clock_out_change_at)->format('H:i') }}</p>
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
                            <p>{{ $applicationDate->attendanceApplication->remark_change }}</p>
                        </td>
                    </tr>
                </table>
            </div>
            @if(empty($applicationDate->approval_at))
                <div class="attendance_button">
                    <form action="/admin/stamp_correction_request/approve" method="post">
                        @csrf
                        <input type="hidden" name="attendance_application_id" value="{{ $applicationDate->attendanceApplication->id }}">
                        <input type="hidden" name="user_id" value="{{ $applicationDate->user->id }}">
                        <button>承認</button>
                    </form>
                </div>
            @elseif(isset($applicationDate->approval_at))
                <div class="approved_message">
                    <p>承認済み</p>
                </div>
            @endif
    </div>
@endsection