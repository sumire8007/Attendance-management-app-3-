@extends('layouts.staff_default')
@section('css')
<link rel="stylesheet" href="{{ asset('css/attendance_list.css') }}">
@endsection

@section('content')
    <div class="attendance_group">
        <div class="attendance_title">
            <h2>勤怠一覧</h2>
        </div>
        <div class="attendance_day">
            <span><p>←前月</p></span>
            <p>📅2023/6/1</p>
            <span><p>翌月→</p></span>
        </div>

        <div class="attendance_table">
            <table>
                <tr>
                    <th>日付</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
                <tr>
                    <td>06/01(木)</td>
                    <td>9:00</td>
                    <td>18:00</td>
                    <td>1:00</td> <!--休憩時間の合計-->
                    <td>8:00</td> <!--勤務時間の合計-->
                    <form action="" method="">
                    <td>
                        <a href="/attendance/id">詳細</a>
                    </td>
                    </form>
                </tr>
                <tr>
                    <td>06/02(金)</td>
                    <td>9:00</td>
                    <td>18:00</td>
                    <td>1:00</td>
                    <td>8:00</td>
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