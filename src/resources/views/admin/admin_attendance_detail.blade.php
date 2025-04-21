@extends('layouts.admin_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/admin_attendance_detail.css') }}">
@endsection

@section('content')

    <div class="attendance_group">
        <div class="attendance_title">
            <h2>勤怠詳細</h2>
        </div>
        <form action="" method="">
            <div class="attendance_table">
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
                        <td>
                            <input type="text"  name="" value="9:00">
                            <p>~</p>
                            <input type="text" name="" value="18:00">
                        </td>
                    </tr>
                    <tr>
                        <th>休憩</th>
                        <td>
                            <input type="text" name="" value="12:00">
                            <p>~</p>
                            <input type="text" name="" value="13:00">
                        </td>
                    </tr>
                    <tr>
                        <th>備考</th>
                        <td class="textarea">
                            <textarea name=""  cols="20" rows="3">電車遅延のため。</textarea>
                        </td>
                    </tr>
                </table>
            </div>
            <div class="attendance_correction_button">
                <button>修正</button>
            </div>
        </form>
    </div>
@endsection