<?php

return [
    // デフォルトのキュー接続先 (非同期処理に database を使用)
    'default' => env('QUEUE_CONNECTION', 'database'),
    // 接続先定義
    'connections' => [
        'sync' => [ 'driver' => 'sync', ], // 同期実行（ローカル開発やテスト用）
        'database' => [ // DBをキューとして使用
            'driver' => 'database',
            'table' => 'jobs', // jobs テーブルを使用 (`queue:table` マイグレーションが必要)
            'queue' => 'default', // デフォルトキュー名
            'retry_after' => 90, // 失敗したジョブを再試行するまでの秒数
            'after_commit' => false,
        ],
        'redis' => [ /* ... Redis 設定 (使用する場合) ... */ ],
        // 他の接続先 (beanstalkd, sqs など)...
    ],
    'batching' => [ /* ... バッチジョブ設定 ... */ ],
    // 失敗したジョブの記録設定
    'failed' => [
        'driver' => env('QUEUE_FAILED_DRIVER', 'database-uuids'), // 失敗ジョブの保存先
        'database' => env('DB_CONNECTION', 'mysql'), // DB接続先
        'table' => 'failed_jobs', // failed_jobs テーブルを使用 (`queue:failed-table` マイグレーションが必要)
    ],
];