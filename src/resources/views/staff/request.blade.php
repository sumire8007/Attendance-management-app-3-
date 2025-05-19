@extends('layouts.staff_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
    <div class="attendance_group">
        @if (request()->is('stamp_correction_request/list'))
                <div class="attendance_title">
                    <h2>申請一覧</h2>
                </div>
                <div class="attendance_status">
                    <a href="/stamp_correction_request/list" class="active_tab">承認待ち</a>
                    <a href="/stamp_correction_request/list/approval">承認済み</a>
                </div>

            <div class="attendance_table">
                <table>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                    @foreach($waitingApprovals as $waitingApproval)
                        <tr>
                            <td>承認待ち</td>
                            <td>{{ $waitingApproval->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($waitingApproval->attendanceApplication->attendance_change_date)->format('Y/m/d') }}
                            </td>
                            <td>{{ $waitingApproval->attendanceApplication->remark_change }}</td>
                            <td>{{ \Carbon\Carbon::parse($waitingApproval->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href="/attendance/{{ $waitingApproval->attendanceApplication->attendance_id }}">詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @elseif(request()->is('stamp_correction_request/list/approval'))
                <div class="attendance_title">
                    <h2>申請一覧</h2>
                </div>
                <div class="attendance_status">
                    <a href="/stamp_correction_request/list" >承認待ち</a>
                    <a href="/stamp_correction_request/list/approval" class="active_tab">承認済み</a>
                </div>

            <div class="attendance_table">
                <table>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                    @foreach($approvals as $approval)
                        <tr>
                            <td>承認済み</td>
                            <td>{{ $approval->user->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($approval->attendanceApplication->attendance_change_date)->format('Y/m/d') }}
                            </td>
                            <td>{{ $approval->attendanceApplication->remark_change }}</td>
                            <td>{{ \Carbon\Carbon::parse($approval->created_at)->format('Y/m/d') }}</td>
                            <td>
                                <a href={{ url('attendance',['attendanceId'=> $approval->attendanceApplication->attendance_id,'applicationId'=> $approval->id ]) }}>詳細</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

@endsection