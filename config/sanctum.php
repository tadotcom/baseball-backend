<?php

use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Str; // Added for Str::random

return [
    // Cookieベース認証を許可するドメイン (Web管理画面用)
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1', // 開発用ドメイン
        // Ensure production domain is included via .env or directly
        env('APP_URL') ? ','.parse_url(env('APP_URL', ''), PHP_URL_HOST) : '' // Add APP_URL host
    ))),

    // SPAセッションの有効期限 (分)
    'expiration' => null, // 通常は session.lifetime を使用

    // SPA認証用ミドルウェア
    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
    ],

    // --- APIトークン (PAT) 設定 ---
    // PATの有効期限 (分) - 24時間 (1440分) に設定
    'token_expiration' => env('SANCTUM_TOKEN_EXPIRATION', 60 * 24), // 1440 minutes

    // PATモデル
    'models' => [
        'personal_access_token' => Laravel\Sanctum\PersonalAccessToken::class,
    ],

    // Transient Token 設定 (今回は未使用)
    // 'transient_token' => [ 'key' => env('SANCTUM_TRANSIENT_TOKEN_KEY', Str::random(40)), ],

    // 認証ガード
    'guard' => ['web'], // デフォルトガード。Sanctumミドルウェアが 'sanctum' を追加
];