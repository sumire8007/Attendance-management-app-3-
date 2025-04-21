@extends('layouts.admin_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/staff_attendance_list.css') }}">
@endsection

@section('content')
    <div>
        <div>
            <h2>勤怠詳細</h2>
        </div>
        <table>
            <tr>
                <th>名前</th>
                <td>西伶奈</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>2023年6月1日</td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>9:00~18:00</td>
            </tr>
            <tr>
                <th>休憩</th>
                <td>12:00~13:00</td>
            </tr>
            <tr>
                <th>備考</th>
                <td>電車遅延のため。</td>
            </tr>
        </table>
        <div>
            <form action="" method="">
                <button>修正</button>　←※押すとデータを直接修正
            </form>
        </div>
    </div>

@endsection