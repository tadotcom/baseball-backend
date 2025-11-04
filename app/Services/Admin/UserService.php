<?php

namespace App\Services\Admin;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // For sending notification
use App\Mail\UserForceDeleted; // Mailable

class UserService
{
    protected UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Get paginated list of users for admin view. (F-ADM-001)
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getUsersPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
         Log::debug("[Admin\UserService] Fetching paginated users.", ['filters' => $filters, 'perPage' => $perPage]);
         // Repository handles filtering logic including soft deletes
        return $this->userRepository->getPaginated($filters, $perPage);
    }

     /**
      * Force delete (soft delete) a user. (F-ADM-003)
      * @param User $user
      * @return void
      * @throws \Exception If deletion fails.
      */
     public function forceDeleteUser(User $user): void
     {
         Log::warning("[Admin\UserService] Force deleting user.", ['admin_id' => auth()->id() ?? 'N/A', 'user_id' => $user->user_id]);
         $deleted = $this->userRepository->softDelete($user);

         if (!$deleted) {
              Log::error("[Admin\UserService] Soft delete failed for user.", ['user_id' => $user->user_id]);
             throw new \Exception("ユーザーの強制退会処理に失敗しました。");
         }

         Log::info("[Admin\UserService] User soft deleted successfully.", ['user_id' => $user->user_id]);

         // Send notification email (MAIL-006)
         try {
             Mail::to($user->email)->queue(new UserForceDeleted($user));
             Log::info("[Admin\UserService] UserForceDeleted email queued.", ['user_id' => $user->user_id]);
         } catch (\Exception $e) {
              Log::error("[Admin\UserService] Failed to queue UserForceDeleted email.", ['user_id' => $user->user_id, 'error' => $e->getMessage()]);
              // Do not fail the operation if email fails
         }
     }

     // TODO: Add methods for finding user details (F-ADM-002),
     // potentially restoring users, etc. if needed.
}