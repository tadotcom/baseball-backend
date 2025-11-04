<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>試合中止のお知らせ</title>
    <style> body { font-family: sans-serif; } </style>
</head>
<body>
    <h2>試合中止のお知らせ</h2>
    <p>{{ $nickname }}様</p>
    <p>ご参加予定でした以下の試合は、主催者の都合（または天候不良など）により中止となりました。</p>
     <div style="border-left: 3px solid #F44336; padding-left: 10px; margin: 15px 0;">
        <p><strong>試合:</strong> {{ $place_name }}</p>
        <p><strong>日時:</strong> {{ $game_date_time }}</p> {{-- Already formatted --}}
    </div>
    <p>ご迷惑をおかけいたしますが、何卒ご了承ください。</p>
    <hr>
    <p style="font-size: small; color: grey;">※本メールは送信専用です。返信はできません。</p>
</body>
</html>