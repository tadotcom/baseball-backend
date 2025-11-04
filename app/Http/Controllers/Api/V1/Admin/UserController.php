<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource; //
use App\Models\User;
use App\Services\Admin\UserService as AdminUserService; // Admin specific service
use Illuminate\Http\Request; // Use base Request for index filtering
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    protected AdminUserService $adminUserService;

    public function __construct(AdminUserService $adminUserService)
    {
        $this->adminUserService = $adminUserService;
    }

    /**
     * Display a paginated listing of users. (F-ADM-001)
     *
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        // TODO: Implement filtering (e.g., by email, nickname, status) if needed
        $filters = $request->only(['email', 'nickname', 'include_deleted']); // Example filters
        $perPage = $request->input('per_page', 20); // Default per page

        try {
            $users = $this->adminUserService->getUsersPaginated($filters, (int)$perPage);

            // Return collection with meta
            return UserResource::collection($users)->additional([
                'meta' => ['timestamp' => now()->toIso8601String()]
            ]);
        } catch (\Exception $e) {
            throw $e; // Let Handler manage response
        }
    }

    /**
     * Display the specified user. (F-ADM-002)
     *
     * @param User $user (Route Model Binding, implicitly handles 404 E-404-01)
     */
    public function show(User $user): UserResource
    {
        try {
            // TODO: Load relationships if needed for admin view (e.g., participations)
            // $user->load('participations.game');
            return (new UserResource($user))->additional([
                'meta' => ['timestamp' => now()->toIso8601String()]
            ]);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Force delete (soft delete) the specified user. (F-ADM-003)
     *
     * @param User $user (Route Model Binding)
     */
    public function destroy(User $user): JsonResponse
    {
        try {
             // Prevent admin from deleting themselves (optional safeguard)
             if ($user->id === auth()->id()) {
                  abort(403, '自身のアカウントを強制退会させることはできません。');
             }

            $this->adminUserService->forceDeleteUser($user); // Service handles soft delete & potentially sending MAIL-006

            // Return 204 No Content on successful delete
            return response()->json(null, 204);

        } catch (\Exception $e) {
            throw $e;
        }
    }
}