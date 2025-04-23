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
                    <td>西伶奈</td>
                </tr>
                <tr>
                    <th>日付</th>
                    <td>2023年　6月1日</td>
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
                    <th>休憩2</th>
                    <td></td>
                </tr>
                <tr>
                    <th>備考</th>
                    <td>電車遅延のため。</td>
                </tr>
            </table>
        </div>
        <div class="attendance_button">
            <form action="" method="">
                <button>承認</button>
            </form>
        </div>
    </div>
@endsection