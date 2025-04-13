<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/admin_default.css') }}" />
    @yield('css')
</head>

<body>
    <header>
        <a class="header_logo" href="/admin/attendance/list"><img src="{{ asset('../../img/logo.png') }}" alt="coachtech"></a>
        <div class="header-content">
            <ul class="header-nav">
                <ol>
                    <a class="header-nav__link-attendance" href="/admin/attendance/list">勤怠一覧</a>
                </ol>
                <ol>
                    <a class="header-nav__link-attendance-list" href="/admin/staff/list">スタッフ一覧</a>
                </ol>
                <ol>
                    <a class="header-nav__link-request" href="/stamp_correction_request/list">申請一覧</a>
                </ol>
                <ol class="header-nav__item-logout">
                    @if (Auth::check())
                        <form action="/logout" method="post">
                            @csrf
                            <button>ログアウト</button>
                        </form>
                    @else
                        <form action="/login" method="get">
                            @csrf
                            <button>ログイン</button>
                        </form>
                    @endif
                </ol>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>