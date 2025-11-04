<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;
use Monolog\Formatter\JsonFormatter; // JSONフォーマット用

return [
    // デフォルトのログチャンネル (stack または daily)
    'default' => env('LOG_CHANNEL', 'daily'), // 設計書では daily を推奨
    // 非推奨機能のログ設定
    'deprecations' => [ 'channel' => env('LOG_DEPRECATIONS_CHANNEL', 'null'), 'trace' => false, ],
    // チャンネル定義
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            // stackチャンネルに含まれるチャンネル
            'channels' => ['daily', 'slack'], // daily と slack を含める
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],
        'daily' => [ // 日次ローテーション
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'), // ログファイルパス
            'level' => env('LOG_LEVEL', 'info'), // 本番環境では info 以上推奨
            'days' => 30, // 30日間保持
            'replace_placeholders' => true,
            // --- JSONフォーマッタ設定 ---
            'formatter' => JsonFormatter::class,
            'formatter_with' => [
                'batchMode' => JsonFormatter::BATCH_MODE_JSON, // JSON形式で出力
                'includeStacktraces' => env('APP_DEBUG', false), // デバッグモード時のみスタックトレースを含める
            ],
            // --- JSONフォーマッタここまで ---
        ],
        'slack' => [ // Slack通知チャンネル
            'driver' => 'slack',
            'url' => env('LOG_SLACK_WEBHOOK_URL'), // Slack Webhook URL (.envで設定)
            'username' => env('APP_NAME', 'Laravel') . ' Log', // 通知時のユーザー名
            'emoji' => ':boom:', // 通知時のアイコン
            'level' => env('LOG_SLACK_LEVEL', 'critical'), // critical レベル以上を通知
            'replace_placeholders' => true,
        ],
        'stderr' => [ // 標準エラー出力 (Dockerなど向け)
            'driver' => 'monolog',
            'level' => env('LOG_LEVEL', 'debug'),
            'handler' => StreamHandler::class,
            'formatter' => env('LOG_STDERR_FORMATTER', JsonFormatter::class), // デフォルトをJSONに
            'with' => [ 'stream' => 'php://stderr', ],
            'processors' => [PsrLogMessageProcessor::class],
             // --- JSONフォーマッタ設定 ---
            'formatter_with' => [
                'batchMode' => JsonFormatter::BATCH_MODE_JSON,
                'includeStacktraces' => env('APP_DEBUG', false),
            ],
             // --- JSONフォーマッタここまで ---
        ],
        // 他のチャンネル (syslog, errorlog, null)...
        'null' => [ 'driver' => 'monolog', 'handler' => NullHandler::class, ], // ログを破棄
    ],
];