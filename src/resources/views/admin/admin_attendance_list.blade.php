@extends('layouts.admin_default')
@section('css')
<link rel="stylesheet" href="{{asset('css/admin_attendance')}}">
@endsection

@section('content')
    <div>
        <div><h2>2023年6月1日の勤怠</h2></div>
        <div> 📅2023/6/1</div>

        <table>
            <tr>
                <th>名前</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
            <tr>
                <td>山田太郎</td>
                <td>9:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td>詳細</td>
            </tr>
            <tr>
                <td>西伶奈</td>
                <td>9:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <td>詳細</td>
            </tr>
        </table>
    </div>

@endsection