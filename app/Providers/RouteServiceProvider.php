<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit; // Rate Limiter Limit クラス
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request; // Request オブジェクト
use Illuminate\Support\Facades\RateLimiter; // Rate Limiter ファサード
use Illuminate\Support\Facades\Route; // Route ファサード
use Illuminate\Support\Facades\Log; // ログ出力用

class RouteServiceProvider extends ServiceProvider
{
    /**
     * アプリケーションの「ホーム」ルートへのパス。
     * 通常、認証後にユーザーがリダイレクトされる場所。
     * (API中心のアプリではあまり使われない可能性があります)
     * @var string
     */
    public const HOME = '/admin/dashboard'; // 管理画面ダッシュボードなど

    /**
     * ルートモデルバインディング、パターンフィルター、その他のルート設定を定義します。
     */
    public function boot(): void
    {
        // アプリケーションのレートリミッターを設定します。
        $this->configureRateLimiting();

        // ルートファイルを読み込みます。
        $this->routes(function () {
            // APIルートを設定します。
            Route::middleware('api') // 標準APIミドルウェアグループ (スロットリングを含む)
                ->prefix('api') // 標準APIプレフィックス
                ->group(base_path('routes/api.php')); // APIルートファイルを読み込む

            // Webルートを設定します (管理画面など)。
            Route::middleware('web') // 標準Webミドルウェアグループ
                ->group(base_path('routes/web.php')); // Webルートファイルを読み込む
        });
    }

    /**
     * アプリケーションのレートリミッターを設定します。
     * 固定ルールに基づいてレートリミットを定義します。
     */
    protected function configureRateLimiting(): void
    {
        // --- 公開/未認証APIエンドポイント用レートリミッター ('api') ---
        RateLimiter::for('api', function (Request $request) {

            // ログイン試行用の特別な制限 (IPベース)
            if ($request->is('api/v1/auth/login')) {
                // Limit::perMinute(5)->by(...) でレート制限を定義し、
                // ->response(...) で制限超過時のカスタムJSONレスポンスを定義
                return Limit::perMinute(5)->by($request->ip())
                       ->response(function (Request $request, array $headers) {
                             Log::warning('[RateLimit] Login attempt limit exceeded.', ['ip' => $request->ip()]);
                             return response()->json([
                                'error' => [
                                    'code' => 'E-429-01', // レートリミット用エラーコード
                                    'message' => 'ログイン試行回数が上限を超えました。しばらくしてから再試行してください。',
                                    'details' => []
                                ],
                                'meta' => ['timestamp' => now()->toIso8601String()]
                             ], 429, $headers); // HTTP 429 Too Many Requests
                       });
            }

            // その他の公開API用の一般的な制限 (IPベース)
            return Limit::perMinute(60)->by($request->ip())
                   ->response(function (Request $request, array $headers) {
                         Log::warning('[RateLimit] Public API limit exceeded.', ['ip' => $request->ip(), 'path' => $request->path()]);
                         return response()->json([
                            'error' => ['code' => 'E-429-01','message' => 'リクエスト回数が上限を超えました。','details' => []],
                            'meta' => ['timestamp' => now()->toIso8601String()]
                         ], 429, $headers);
                   });
        });

        // --- 認証済みAPIエンドポイント用レートリミッター ('api_authenticated') ---
        RateLimiter::for('api_authenticated', function (Request $request) {
            // 認証済みユーザーIDに基づいて制限を適用し、未認証の場合はIPベースにフォールバック
            //
            $limit = $request->user()
                    ? Limit::perMinute(120)->by($request->user()->getAuthIdentifier()) // ユーザーIDあたり毎分120回
                    : Limit::perMinute(60)->by($request->ip()); // 安全のためのフォールバック

            // Limitオブジェクトに対してカスタム応答を設定
            return $limit->response(function (Request $request, array $headers) {
                         Log::warning('[RateLimit] Authenticated API limit exceeded.', [
                            'user_id' => $request->user()?->getAuthIdentifier(),
                            'ip' => $request->ip(),
                            'path' => $request->path()
                         ]);
                         return response()->json([
                             'error' => ['code' => 'E-429-01','message' => 'リクエスト回数が上限を超えました。','details' => []],
                             'meta' => ['timestamp' => now()->toIso8601String()]
                         ], 429, $headers);
                   });
        });
    }
}