<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠管理</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/email_verify.css') }}" />
</head>

<body>
    <header>
        <img src="{{ asset('../../img/logo.png') }}" alt="coachtech">
    </header>
<main>
    <div class="message_box">
        <p>登録していただいたメールアドレスに認証メールを送付しました。</p>
        <p>メール認証を完了してください。</p>
        <div class="auth_button">
            <a class="auth-transition" href="http://localhost:8025/">認証はこちらから</a>
        </div>
        <form action="/email/resend" method="post">
            @csrf
            <button class="resend-link">認証メールを再送する</button>
        </form>
    </div>
</main>