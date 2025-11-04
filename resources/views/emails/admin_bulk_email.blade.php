<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title> {{-- Use subject as title --}}
    <style> body { font-family: sans-serif; } </style>
</head>
<body>
    {{-- No specific greeting like '様' as it's bulk mail --}}
    {{-- <p>{{ $nickname }}様</p> --}}

    {{-- Render the raw HTML body provided by the admin --}}
    {!! $body !!} {{-- Use {!! !!} to output raw HTML (Ensure admin input is sanitized if necessary!) --}}

    <hr>
    <p style="font-size: small; color: grey;">※本メールは草野球マッチング運営事務局より配信しています。</p>
    <p style="font-size: small; color: grey;">※本メールは送信専用です。返信はできません。</p>
    {{-- TODO: Add unsubscribe link if legally required --}}
</body>
</html>