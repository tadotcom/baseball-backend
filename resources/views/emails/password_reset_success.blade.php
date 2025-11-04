<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>パスワードリセット完了</title>
    <style> body { font-family: sans-serif; } </style>
</head>
<body>
    <h2>パスワードリセット完了</h2>
    <p>{{ $nickname }}様</p>
    <p>パスワードの再設定が正常に完了しました。</p>
    <p>新しいパスワードでアプリにログインしてください。</p>
    {{-- TODO: Add link to app --}}
    <hr>
    <p style="font-size: small; color: grey;">※本メールは送信専用です。返信はできません。</p>
</body>
</html>