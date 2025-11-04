<?php

use Illuminate\Support\Str;

return [
    'default' => env('DB_CONNECTION', 'mysql'), // デフォルト接続先
    'connections' => [
        'sqlite' => [ /* ... SQLite 設定 ... */ ],
        'mysql' => [ // MySQL 設定
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4', // 文字コード
            'collation' => 'utf8mb4_unicode_ci', // 照合順序
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => 'InnoDB', // ストレージエンジン
            'options' => extension_loaded('pdo_mysql') ? array_filter([ PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'), ]) : [],
            // トランザクション分離レベル（MySQL 8.0 のデフォルトは REPEATABLE READ）
        ],
        // 他の接続設定 (pgsql, sqlsrv)...
    ],
    'migrations' => [ 'table' => 'migrations', 'update_date_on_publish' => true, ], // マイグレーション設定
    'redis' => [ /* ... Redis 設定 (使用する場合) ... */ ],
];