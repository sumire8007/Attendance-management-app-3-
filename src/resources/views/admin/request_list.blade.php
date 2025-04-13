@extends('layouts.admin_default')
@section('css')
    <link rel="stylesheet" href="{{ asset('css/request_list.css') }}">
@endsection

@section('content')
    <div>
        <div><h2>申請一覧</h2></div>
        <div>
            <a href="">承認待ち</a>
            <a href="">承認済み</a>
        </div>
            <table>
                <tr>
                    <th>状態</th>
                    <th>名前</th>
                    <th>対象日時</th>
                    <th>申請理由</th>
                    <th>申請日時</th>
                    <th>詳細</th>
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>西伶奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <!-- <form action=""> -->
                    <td>
                        <a href="/admin/stamp_correction_request/approve/">詳細</a>
                    </td>
                    <!-- </form> -->
                </tr>
                <tr>
                    <td>承認待ち</td>
                    <td>西伶奈</td>
                    <td>2023/06/01</td>
                    <td>遅延のため</td>
                    <td>2023/06/02</td>
                    <!-- <form action=""> -->
                    <td>
                        <a href="/admin/stamp_correction_request/approve/">詳細</a>
                    </td>
                    <!-- </form> -->
                </tr>
            </table>
    </div>
@endsection