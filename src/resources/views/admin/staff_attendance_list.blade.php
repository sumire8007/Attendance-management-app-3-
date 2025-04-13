@extends('layouts.admin_default')
@section('css')
<link rel="stylesheet" href="{{ asset('css/staff_attendance_list.css') }}">
@endsection

@section('content')
    <div>
        <div>
            <h2>西伶奈さんの勤怠</h2>
        </div>
        <div> 📅2023/06</div>

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
                <!-- <form action=""> -->
                <td>
                    <a href="/admin/attendance/id">詳細</a>
                </td>
                <!-- </form> -->
            </tr>
            <tr>
                <td>06/02(金)</td>
                <td>9:00</td>
                <td>18:00</td>
                <td>1:00</td>
                <td>8:00</td>
                <!-- <form action=""> -->
                <td>
                    <a href="/admin/attendance/id">詳細</a>
                </td>
                <!-- </form> -->
            </tr>
        </table>
        <div>
            <form action="" method="">
                <button>CSV出力</button>
            </form>
        </div>
    </div>

@endsection