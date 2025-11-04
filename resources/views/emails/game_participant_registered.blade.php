<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>試合参加登録完了</title>
    <style> body { font-family: sans-serif; } </style>
</head>
<body>
    <h2>試合参加登録完了</h2>
    <p>{{ $nickname }}様</p>
    <p>以下の試合への参加登録が完了しました。</p>
    <div style="border-left: 3px solid #4CAF50; padding-left: 10px; margin: 15px 0;">
        <p><strong>試合:</strong> {{ $place_name }}</p>
        <p><strong>日時:</strong> {{ $game_date_time }}</p> {{-- Already formatted in Mailable --}}
        <p><strong>場所:</strong> {{ $address }}</p>
        <p><strong>参加費:</strong> {{ $fee == 0 ? '無料' : number_format($fee) . '円' }}</p>
    </div>
    <p>当日は「チェックイン」機能を利用するため、アプリを忘れずにお持ちください。</p>
    <hr>
    <p style="font-size: small; color: grey;">※本メールは送信専用です。返信はできません。</p>
</body>
</html>