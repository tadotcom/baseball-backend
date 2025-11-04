<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>アカウント登録完了</title>
    {{-- TODO: Add basic styling --}}
    <style> body { font-family: sans-serif; } </style>
</head>
<body>
    <h2>アカウント登録完了</h2>
    <p>{{ $nickname }}様</p>
    <p>草野球マッチングアプリへのご登録ありがとうございます。</p>
    <p>
        以下のニックネームで登録が完了しました。<br>
        ニックネーム: <strong>{{ $nickname }}</strong>
    </p>
    <p>アプリを開いて試合を探しましょう！</p>
    {{-- TODO: Add link to app store or website --}}
    {{-- <p><a href="#">アプリを開く</a></p> --}}
    <hr>
    <p style="font-size: small; color: grey;">※本メールは送信専用です。返信はできません。</p>
</body>
</html>