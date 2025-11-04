<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request; // Import Request
use Illuminate\Auth\AuthenticationException; // 認証エラー
use Illuminate\Validation\ValidationException; // バリデーションエラー
use Illuminate\Auth\Access\AuthorizationException; // 認可エラー
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException; // 404エラー
use Symfony\Component\HttpKernel\Exception\HttpException; // 一般的なHTTPエラー
use Illuminate\Support\Facades\Log; // ログ出力用
use Throwable; // PHP 7+

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        // 必要に応じてログに出力しない例外を定義
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     * ここで例外の種類ごとにレポート（ログ出力）やレンダリング（レスポンス返却）をカスタマイズする
     */
    public function register(): void
    {
        // --- 例外のレポート（ログ出力）設定 ---
        $this->reportable(function (Throwable $e) {
            // APIリクエストで発生したサーバーエラー(5xx)などをログに記録
            if ($this->shouldReport($e) && app()->bound('log')) {
                 // 認証済みユーザーがいれば、ユーザーIDもログに追加
                 $userId = auth()->check() ? auth()->user()->getAuthIdentifier() : 'guest';
                 Log::error( //
                     $e->getMessage(),
                     array_merge($this->context(), [
                        'exception_class' => get_class($e),
                        'user_id' => $userId,
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                        // 可能であればエラーコードを取得 (カスタム例外など)
                        'error_code' => method_exists($e, 'getErrorCode') ? $e->getErrorCode() : 'N/A' //
                     ])
                 );
             }
        });

        // --- 例外のレンダリング（レスポンス返却）設定 ---
        $this->renderable(function (Throwable $e, Request $request) {
            // APIリクエスト (JSONを期待) の場合のみ特別処理
            if ($request->expectsJson()) {
                return $this->handleApiException($e);
            }

            // Webリクエストの場合はLaravelのデフォルトレンダリングに任せる
            return parent::render($request, $e);
        });
    }


    /**
     * APIリクエストで発生した例外を適切なJSONレスポンスに変換する
     *
     * @param Throwable $e
     * @return \Illuminate\Http\JsonResponse
     */
    protected function handleApiException(Throwable $e): \Illuminate\Http\JsonResponse
    {
        $statusCode = $this->isHttpException($e) ? $e->getStatusCode() : 500;
        $errorCode = 'E-XXX-XX'; // デフォルト
        $message = 'エラーが発生しました';
        $details = [];

        // 例外の種類に応じてエラーコード、メッセージ、ステータスコードを設定
        if ($e instanceof AuthenticationException) {
            $statusCode = 401;
            $errorCode = 'E-401-01'; //
            $message = '認証に失敗しました';
        } elseif ($e instanceof AuthorizationException) {
            $statusCode = 403;
            $errorCode = 'E-403-01'; //
            $message = 'アクセス権限がありません';
        } elseif ($e instanceof NotFoundHttpException) {
            $statusCode = 404;
             // ルートが見つからない場合とモデルが見つからない場合を区別
             if (str_contains(strtolower($e->getMessage()), 'not found')) {
                 $errorCode = 'E-404-0X'; // APIエンドポイントが見つからない場合のコードを定義 (例: E-404-00)
                 $message = '指定されたAPIエンドポイントが見つかりません';
             } else {
                 // ModelNotFoundException は NotFoundHttpException に変換されることが多い
                 // エラーコードはリソースによって変える (User: E-404-01, Game: E-404-02)
                 // ここでは汎用的なメッセージにするか、例外メッセージから推測する
                 $errorCode = 'E-404-0X'; // リソースが見つからない場合の汎用コード
                 $message = '指定されたリソースが見つかりません';
             }
        } elseif ($e instanceof ValidationException) {
            $statusCode = 422;
            $errorCode = 'E-422-XX'; // バリデーションエラーの汎用コード (詳細はdetailsで示す)
            $message = '入力内容に誤りがあります';
            // Laravelのバリデーションエラー詳細を取得
            foreach ($e->errors() as $field => $errorMessages) {
                 // エラーメッセージからエラーコードを推測 (FormRequestのmessages()に基づく)
                 $fieldErrorCode = $this->guessErrorCodeFromValidationMessage($errorMessages[0] ?? '');
                 $details[] = [
                     'field' => $field,
                     'message' => $errorMessages[0] ?? 'バリデーションエラー',
                     'code' => $fieldErrorCode // 推測したコード
                 ];
                 // 最初のフィールドエラーコードを代表として使う (オプション)
                 if ($errorCode === 'E-422-XX' && $fieldErrorCode !== 'E-422-XX') {
                     $errorCode = $fieldErrorCode;
                 }
            }
            // FormRequestのmessages()で直接エラーコードを設定していればそれを使う
            if (isset($details[0]['code']) && str_starts_with($details[0]['code'], 'E-422-')) {
                $errorCode = $details[0]['code'];
                $message = $details[0]['message']; // 最初のエラーメッセージを代表にする
            }

        } elseif ($this->isHttpException($e)) {
             // その他のHTTP例外 (400 Bad Request, 405 Method Not Allowed, 429 Too Many Requests など)
             $statusCode = $e->getStatusCode();
             // HttpExceptionのメッセージをそのまま使うか、ステータスコードから汎用メッセージを生成
             $message = $e->getMessage() ?: $this->getDefaultHttpMessage($statusCode);
             // エラーコードもステータスコードから汎用コードを生成 (例: E-400-01)
             $errorCode = $this->getDefaultHttpErrorCode($statusCode);

             // レートリミット(429)の場合
             if ($statusCode === 429) {
                 $errorCode = 'E-429-01'; // レートリミット用エラーコードを定義
                 $message = 'リクエスト回数が上限を超えました。しばらくしてから再試行してください。';
             }
        } else {
            // 上記以外の予期せぬエラー (500 Internal Server Error)
            $statusCode = 500;
            $errorCode = 'E-500-03'; //
            // 本番環境では詳細なエラーメッセージを返さない
            $message = config('app.debug') ? $e->getMessage() : 'サーバーエラーが発生しました';
        }

        // --- JSONレスポンス構築 ---
        $response = [
            'error' => [
                'code' => $errorCode,
                'message' => $message,
            ],
            'meta' => [
                'timestamp' => now()->toIso8601String(),
            ],
        ];

        // バリデーションエラーの場合のみ details を追加
        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * HTTPステータスコードに対応するデフォルトのメッセージを取得
     */
    protected function getDefaultHttpMessage(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'リクエストが不正です',
            405 => '許可されていないメソッドです',
            429 => 'リクエスト回数が上限を超えました',
            default => 'エラーが発生しました',
        };
    }
     /**
     * HTTPステータスコードに対応するデフォルトのエラーコードを取得
     */
    protected function getDefaultHttpErrorCode(int $statusCode): string
    {
        return match ($statusCode) {
            400 => 'E-400-01', //
            // 他の汎用コードを定義...
            default => 'E-' . $statusCode . '-XX', // 汎用形式
        };
    }

    /**
     * バリデーションメッセージからエラーコードを推測するヘルパー (簡易版)
     */
     protected function guessErrorCodeFromValidationMessage(string $message): string
     {
         if (preg_match('/^(E-\d{3}-\d{2}):\s*/', $message, $matches)) {
             return $matches[1];
         }
         return 'E-422-XX'; // 推測できない場合は汎用コード
     }

}