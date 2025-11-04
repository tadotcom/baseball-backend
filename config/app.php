<?php

use Illuminate\Support\Facades\Facade;
use Illuminate\Support\ServiceProvider;

return [
    'name' => env('APP_NAME', 'Laravel'), // アプリケーション名
    'env' => env('APP_ENV', 'production'), // 環境 (production, local など)
    'debug' => (bool) env('APP_DEBUG', false), // デバッグモード
    'url' => env('APP_URL', 'http://localhost'), // アプリケーションURL
    'asset_url' => env('ASSET_URL'), // アセットURL
    'timezone' => 'Asia/Tokyo', // タイムゾーンを日本時間に設定
    'locale' => 'ja', // デフォルト言語を日本語に設定
    'fallback_locale' => 'en', // フォールバック言語
    'faker_locale' => 'ja_JP', // Faker（テストデータ生成）用ロケール
    'key' => env('APP_KEY'), // アプリケーションキー
    'cipher' => 'AES-256-CBC', // 暗号化方式
    'maintenance' => [ 'driver' => 'file', ], // メンテナンスモード設定
    // サービスプロバイダの登録
    'providers' => ServiceProvider::defaultProviders()->merge([
        // アプリケーションサービスプロバイダ...
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class, // ブロードキャスト使用時
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class, // ルート設定
        // カスタムプロバイダをここに追加
    ])->toArray(),
    // ファサードエイリアスの登録
    'aliases' => Facade::defaultAliases()->merge([
        // カスタムエイリアス...
        // 'Distance' => App\Utils\DistanceCalculator::class, // 例
    ])->toArray(),
    // フロントエンドURL（パスワードリセットメールで使用）
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:8080'), // Flutter開発サーバーのURLなど
];