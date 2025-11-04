<?php

return [
    // mailgun, postmark, ses ...

    // --- Firebase 設定 ---
    // kreait/laravel-firebase パッケージを使用する場合の設定例
    'firebase' => [
        // Firebase サービスアカウントキー (JSONファイル) のパス
        // storage/app など、公開ディレクトリ外の安全な場所に配置
        'credentials' => [
            'file' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase_credentials.json')),
            // 'auto_discovery' => true, // 自動検出オプション
        ],
        // Firebase プロジェクトID (通常はクレデンシャルから推測される)
        // 'project_id' => env('FIREBASE_PROJECT_ID'),

        // --- FCM Admin SDK 設定 (kreait/firebase-php) ---
        // (kreait/laravel-firebase があれば通常は上記クレデンシャルで動作)

        // --- laravel-notification-channels/fcm パッケージを使用する場合の設定例 ---
        // 'fcm' => [
        //     'driver' => 'fcm',
        //     // Firebase コンソールから取得したサーバーキー (.env で設定)
        //     'key' => env('FCM_SERVER_KEY'),
        // ],
        // --- End laravel-notification-channels/fcm ---
    ],
    // --- Firebase 設定ここまで ---

    // 他のサービス (Stripe, GitHub など)...

];