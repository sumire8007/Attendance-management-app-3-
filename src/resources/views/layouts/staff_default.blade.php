<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/staff_default.css') }}" />
    @yield('css')
</head>

<body>
    <header>
        <a class="header_logo" href="/attendance"><img src="{{ asset('../../img/logo.png') }}" alt="coachtech"></a>
        <div class="header-content">
            <ul class="header-nav">
                <ol>
                    <a class="header-nav__link-attendance" href="/attendance">勤怠</a>
                </ol>
                <ol>
                    <a class="header-nav__link-attendance-list" href="/attendance/list">勤怠一覧</a>
                </ol>
                <ol>
                    <a class="header-nav__link-request" href="/stamp_correction_request/list">申請</a>
                </ol>
                <ol class="header-nav__item-logout">
                    <form action="/logout" method="post">
                        @csrf
                        <button>ログアウト</button>
                    </form>
                </ol>
            </ul>
        </div>
    </header>

    <main>
        @yield('content')
    </main>
</body>

</html>