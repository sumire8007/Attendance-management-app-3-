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
                <tr>
                    <td>西伶奈</td>
                    <td>reina.n@coachtech.com</td>
                    <td><a href="/admin/attendance/staff/id">詳細</a></td>
                </tr>
                <tr>
                    <td>山田太郎</td>
                    <td>taro.y@coachtech.com</td>
                    <td><a href="/admin/attendance/staff/id">詳細</a></td>
                </tr>
                <tr>
                    <td>増田一世</td>
                    <td>issei.m@coachtech.com</td>
                    <td><a href="/admin/attendance/staff/id">詳細</a></td>
                </tr>

            </table>
        </div>
    </div>
@endsection