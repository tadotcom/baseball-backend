<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>パスワードリセットのご案内</title>
    <style> body { font-family: sans-serif; } </style>
</head>
<body>
    <h2>パスワードリセットのご案内</h2>
    <p>{{ $nickname }}様</p>
    <p>パスワードリセットのリクエストを受け付けました。</p>
    <p>
        以下のリンクをクリックして、パスワードの再設定を完了してください。<br>
        このリンクの有効期限は{{ config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60) }}分です。{{-- Get expiry from config --}}
    </p>
    <p>
        {{-- Button-like link --}}
        <a href="{{ $reset_url }}" style="display: inline-block; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">パスワードをリセットする</a>
    </p>
    <p>
        もし上記ボタンをクリックできない場合は、以下のURLをコピーしてブラウザに貼り付けてください:<br>
        <a href="{{ $reset_url }}">{{ $reset_url }}</a>
    </p>
     <p style="font-size: small; color: grey;">
        (このリンクはアプリで開かれることを想定しています。もしWebで開かれた場合は、アプリでの操作を促してください。)
     </p>
    <hr>
    <p style="font-size: small; color: grey;">※本メールアドレスにお心当たりのない場合、本メールは破棄してください。</p>
    <p style="font-size: small; color: grey;">※本メールは送信専用です。返信はできません。</p>
</body>
</html>