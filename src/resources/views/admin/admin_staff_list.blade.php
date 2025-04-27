@extends('layouts.admin_default')
@section('css')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')

    <div class="attendance_group">
        <div class="attendance_title">
            <h2>スタッフ一覧</h2>
        </div>
        <div class="attendance_table">
            <table>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>

                @foreach ($users as $user )
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td><a href="/admin/attendance/staff/id">詳細</a></td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
@endsection