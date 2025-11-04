<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\NotificationLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificationController extends Controller
{
    /**
     * 通知送信履歴一覧
     */
    public function index(Request $request)
    {
        $query = NotificationLog::query()->with('game');

        // フィルター: 通知種類
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // フィルター: 配信対象
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // フィルター: ステータス
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 最新順でページネーション
        $logs = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.notifications.index', compact('logs'));
    }

    /**
     * 新規通知作成フォーム
     */
    public function create()
    {
        // 開催予定の試合を取得（日時順）
        $games = Game::where('game_date_time', '>=', now())
            ->orderBy('game_date_time', 'asc')
            ->get();

        return view('admin.notifications.create', compact('games'));
    }

    /**
     * 通知送信履歴詳細
     */
    public function show(NotificationLog $notificationLog)
    {
        $notificationLog->load('game');
        return view('admin.notifications.show', ['log' => $notificationLog]);
    }

    /**
     * プッシュ通知送信
     */
    public function sendPush(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:all,game',
            'game_id' => 'required_if:target_type,game|exists:games,game_id',
            'title' => 'required|string|max:100',
            'body' => 'required|string|max:500',
        ], [
            'target_type.required' => '配信対象を選択してください',
            'game_id.required_if' => '試合を選択してください',
            'title.required' => 'タイトルを入力してください',
            'title.max' => 'タイトルは100文字以内で入力してください',
            'body.required' => '本文を入力してください',
            'body.max' => '本文は500文字以内で入力してください',
        ]);

        DB::beginTransaction();
        try {
            // 通知ログを作成
            $log = NotificationLog::create([
                'type' => 'push',
                'target_type' => $request->target_type,
                'game_id' => $request->game_id,
                'title' => $request->title,
                'body' => $request->body,
                'sent_by_admin' => Auth::user()->email,
                'status' => '送信中',
            ]);

            // 送信対象ユーザーを取得
            $users = $this->getTargetUsers($request->target_type, $request->game_id);

            // プッシュ通知を送信
            $result = $this->sendPushNotifications($users, $request->title, $request->body);

            // ログを更新
            $log->update([
                'sent_count' => $result['success'],
                'failed_count' => $result['failed'],
                'status' => $result['failed'] > 0 ? '送信失敗' : '送信完了',
                'error_message' => $result['error'] ?? null,
            ]);

            DB::commit();

            if ($result['failed'] > 0) {
                return redirect()->route('admin.notifications.index')
                    ->with('warning', "プッシュ通知を送信しました。成功: {$result['success']}件、失敗: {$result['failed']}件");
            }

            return redirect()->route('admin.notifications.index')
                ->with('success', "プッシュ通知を{$result['success']}件送信しました。");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('プッシュ通知送信エラー: ' . $e->getMessage());
            
            if (isset($log)) {
                $log->update([
                    'status' => '送信失敗',
                    'error_message' => $e->getMessage(),
                ]);
            }

            return redirect()->back()
                ->with('error', 'プッシュ通知の送信に失敗しました: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * メール送信
     */
    public function sendEmail(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:all,game',
            'game_id' => 'required_if:target_type,game|exists:games,game_id',
            'subject' => 'required|string|max:200',
            'body' => 'required|string|max:5000',
        ], [
            'target_type.required' => '配信対象を選択してください',
            'game_id.required_if' => '試合を選択してください',
            'subject.required' => '件名を入力してください',
            'subject.max' => '件名は200文字以内で入力してください',
            'body.required' => '本文を入力してください',
            'body.max' => '本文は5000文字以内で入力してください',
        ]);

        DB::beginTransaction();
        try {
            // 通知ログを作成
            $log = NotificationLog::create([
                'type' => 'email',
                'target_type' => $request->target_type,
                'game_id' => $request->game_id,
                'title' => $request->subject,
                'body' => $request->body,
                'sent_by_admin' => Auth::user()->email,
                'status' => '送信中',
            ]);

            // 送信対象ユーザーを取得
            $users = $this->getTargetUsers($request->target_type, $request->game_id);

            // メールを送信
            $result = $this->sendEmails($users, $request->subject, $request->body);

            // ログを更新
            $log->update([
                'sent_count' => $result['success'],
                'failed_count' => $result['failed'],
                'status' => $result['failed'] > 0 ? '送信失敗' : '送信完了',
                'error_message' => $result['error'] ?? null,
            ]);

            DB::commit();

            if ($result['failed'] > 0) {
                return redirect()->route('admin.notifications.index')
                    ->with('warning', "メールを送信しました。成功: {$result['success']}件、失敗: {$result['failed']}件");
            }

            return redirect()->route('admin.notifications.index')
                ->with('success', "メールを{$result['success']}件送信しました。");

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('メール送信エラー: ' . $e->getMessage());
            
            if (isset($log)) {
                $log->update([
                    'status' => '送信失敗',
                    'error_message' => $e->getMessage(),
                ]);
            }

            return redirect()->back()
                ->with('error', 'メールの送信に失敗しました: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * 送信対象ユーザーを取得
     */
    private function getTargetUsers(string $targetType, ?string $gameId): \Illuminate\Support\Collection
    {
        if ($targetType === 'all') {
            // 全ユーザー（削除済みを除く）
            return User::whereNull('deleted_at')->get();
        } else {
            // 特定試合の参加者
            $game = Game::with('participants.user')->findOrFail($gameId);
            return $game->participants->pluck('user')->filter();
        }
    }

    /**
     * プッシュ通知を送信
     */
    private function sendPushNotifications($users, string $title, string $body): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                // TODO: 実際のプッシュ通知送信処理を実装
                // 例: Firebase Cloud Messaging (FCM) や OneSignal などを使用
                
                // 仮実装: fcm_tokenがある場合のみ成功とみなす
                if (!empty($user->fcm_token)) {
                    // ここに実際の送信ロジックを実装
                    // $this->sendFcmNotification($user->fcm_token, $title, $body);
                    $success++;
                } else {
                    $failed++;
                    $errors[] = "ユーザーID {$user->user_id}: FCMトークンが未登録";
                }

            } catch (\Exception $e) {
                $failed++;
                $errors[] = "ユーザーID {$user->user_id}: " . $e->getMessage();
                Log::error("プッシュ通知送信失敗 (ユーザーID: {$user->user_id}): " . $e->getMessage());
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'error' => !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null,
        ];
    }

    /**
     * メールを送信
     */
    private function sendEmails($users, string $subject, string $body): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        foreach ($users as $user) {
            try {
                // TODO: 実際のメール送信処理を実装
                // 例: Laravel の Mail ファサードを使用
                
                // 仮実装: メールアドレスがある場合のみ成功とみなす
                if (!empty($user->email)) {
                    // ここに実際の送信ロジックを実装
                    // Mail::to($user->email)->send(new NotificationMail($subject, $body));
                    $success++;
                } else {
                    $failed++;
                    $errors[] = "ユーザーID {$user->user_id}: メールアドレスが未登録";
                }

            } catch (\Exception $e) {
                $failed++;
                $errors[] = "ユーザーID {$user->user_id}: " . $e->getMessage();
                Log::error("メール送信失敗 (ユーザーID: {$user->user_id}): " . $e->getMessage());
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'error' => !empty($errors) ? implode('; ', array_slice($errors, 0, 5)) : null,
        ];
    }
}