<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
// TODO: Create specific Requests for validation
use App\Http\Requests\Admin\StorePushNotificationRequest; //
use App\Http\Requests\Admin\StoreEmailNotificationRequest; //
// TODO: Create Admin\NotificationService
use App\Services\Admin\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Send bulk push notifications. (F-ADM-009)
     *
     */
    public function sendPush(StorePushNotificationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        // validatedData should contain 'title', 'body', and target criteria (e.g., 'all_users', 'game_id', 'user_ids')

        try {
            // Service handles finding target users and sending notifications via FCM
            $result = $this->notificationService->sendBulkPushNotification(
                $validatedData['title'],
                $validatedData['body'],
                $validatedData['target'] ?? ['type' => 'all'] // Define target structure in Request
            );

            if ($result['status'] === 'failed') {
                abort(500, 'E-500-01: 通知の配信に一部失敗しました'); //
            }

            return response()->json([
                'data' => [
                    'message' => 'プッシュ通知を配信しました。',
                    'success_count' => $result['success_count'] ?? 0,
                    'failure_count' => $result['failure_count'] ?? 0,
                ],
                'meta' => ['timestamp' => now()->toIso8601String()]
            ], 202); // 202 Accepted as it might be queued

        } catch (\Exception $e) {
             throw $e;
        }
    }

     /**
     * Send bulk email notifications. (F-ADM-010)
     *
     */
    public function sendEmail(StoreEmailNotificationRequest $request): JsonResponse
    {
        $validatedData = $request->validated();
        // validatedData should contain 'subject', 'body' (HTML), and target criteria

        try {
             // Service handles finding target users and queueing emails
             $result = $this->notificationService->sendBulkEmail(
                 $validatedData['subject'],
                 $validatedData['body'], // HTML body
                 $validatedData['target'] ?? ['type' => 'all']
             );

              if ($result['status'] === 'failed') {
                 abort(500, 'E-500-02: メールの配信キューイングに一部失敗しました'); //
              }

             return response()->json([
                 'data' => [
                     'message' => 'メール配信をキューに追加しました。',
                     'queued_count' => $result['queued_count'] ?? 0,
                 ],
                 'meta' => ['timestamp' => now()->toIso8601String()]
             ], 202); // 202 Accepted

        } catch (\Exception $e) {
             throw $e;
        }
    }
}