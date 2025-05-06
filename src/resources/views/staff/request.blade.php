@extends('layouts.staff_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/request.css') }}">
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_title">
            <h2>申請一覧</h2>
        </div>
        <div class="attendance_status">
            <a href="/stamp_correction_request/list">承認待ち</a>
            <a href="/stamp_correction_request/list">承認済み</a>
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
                        <td>{{ $waitingApproval->attendanceApplications->attendances->user->name }}</td>
                        <td>{{ $waitingApproval->attendanceApplications->attendances->attendance_date }}</td>
                        <td>{{ $waitingApproval->attendanceApplications->remark_change }}</td>
                        <td>{{ $waitingApproval->created_at }}</td>
                        <td>
                            <a href="/attendance/{{ $waitingApproval->attendanceApplication['id'] }}">詳細</a>
                        </td>
                    </tr>

                @endforeach
                <tr>
                    <td>承認待ち</td>
                    <td>西伶奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <form action="">
                    <td>
                        <a href="/attendance/id">詳細</a>
                    </td>
                    </form>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>西伶奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <form action="">
                    <td>
                        <a href="/attendance/id">詳細</a>
                    </td>
                    </form>
                </tr>
            </table>
        </div>
    </div>

@endsection