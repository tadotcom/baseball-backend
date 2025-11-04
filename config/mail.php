<?php

return [
    'default' => env('MAIL_MAILER', 'smtp'), // デフォルトメーラー
    // メーラー設定
    'mailers' => [
        'smtp' => [ // SMTP設定 (Xserver用)
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', 'smtp.mailgun.org'), // .env で Xserver のホストを設定
            'port' => env('MAIL_PORT', 587), // .env でポートを設定 (587)
            'encryption' => env('MAIL_ENCRYPTION', 'tls'), // .env で暗号化方式を設定 (tls)
            'username' => env('MAIL_USERNAME'), // .env で SMTP ユーザー名を設定
            'password' => env('MAIL_PASSWORD'), // .env で SMTP パスワードを設定
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN'),
        ],
        'log' => [ /* ... ログメーラー設定 ... */ ],
        'array' => [ /* ... アレイメーラー設定 ... */ ],
        // 他のメーラー (ses, mailgun など)...
    ],
    // デフォルト送信元情報
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'), // .env で設定
        'name' => env('MAIL_FROM_NAME', 'Example'), // .env で設定 (APP_NAME を使う)
    ],
    'markdown' => [ /* ... Markdownメール設定 ... */ ],
];