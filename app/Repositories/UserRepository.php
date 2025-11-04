<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash; // For password hashing during creation

class UserRepository
{
    /**
     * Find a user by their ID.
     * @param string $userId
     * @param bool $withTrashed Include soft-deleted users.
     * @return User|null
     */
    public function findById(string $userId, bool $withTrashed = false): ?User
    {
        $query = $withTrashed ? User::withTrashed() : User::query();
        return $query->find($userId); // Assumes $userId is the primary key 'user_id'
    }

    /**
     * Find a user by their email address.
     * @param string $email
     * @param bool $withTrashed Include soft-deleted users.
     * @return User|null
     */
    public function findByEmail(string $email, bool $withTrashed = false): ?User
    {
        $query = $withTrashed ? User::withTrashed() : User::query();
        return $query->where('email', $email)->first();
    }

     /**
     * Get a paginated list of users with optional filters.
     * Used by Admin API F-ADM-001
     * @param array $filters (e.g., ['email' => '...', 'nickname' => '...', 'include_deleted' => true])
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = User::query();

        // Handle including soft-deleted users
        if (!empty($filters['include_deleted']) && $filters['include_deleted']) {
            $query->withTrashed();
        }

        // Apply filters
        if (!empty($filters['email'])) {
            $query->where('email', 'like', '%' . $filters['email'] . '%');
        }
        if (!empty($filters['nickname'])) {
            $query->where('nickname', 'like', '%' . $filters['nickname'] . '%');
        }

        // Default sorting
        $query->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }


    /**
     * Create a new user.
     * Used by AuthService during registration (F-USR-001)
     * @param array $data ('email', 'password', 'nickname')
     * @return User
     */
    public function create(array $data): User
    {
        // Password hashing is handled by the User model's $casts property
        // UUID generation is handled by the User model's boot method
        return User::create([
            'email' => $data['email'],
            'password' => $data['password'], // Model will hash this
            'nickname' => $data['nickname'],
        ]);
    }

    /**
     * Soft delete a user. (F-ADM-003)
     * @param User $user
     * @return bool|null Result of the delete operation.
     */
    public function softDelete(User $user): ?bool
    {
        // SoftDeletes trait handles setting deleted_at
        return $user->delete();
    }

    // Optional: Add update method if user profile updates are needed
    // public function update(User $user, array $data): bool { ... }
}