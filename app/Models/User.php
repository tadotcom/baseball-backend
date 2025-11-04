<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; // For logical deletion
use Illuminate\Database\Eloquent\Prunable; // For physical deletion after 60 days
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // For API authentication
use Illuminate\Support\Str; // For UUID generation

class User extends Authenticatable
{
    // --- Traits ---
    //
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Prunable;

    // --- Table Configuration ---
    protected $table = 'users'; // Explicitly define table name
    protected $primaryKey = 'user_id'; // Define primary key
    public $incrementing = false; // Set to false for UUIDs
    protected $keyType = 'string'; // Set key type to string for UUIDs

    /**
     * The attributes that are mass assignable.
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id', 
        'email',
        'password',
        'nickname',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        // 'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed', // Automatically hashes passwords
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime', // For SoftDeletes
    ];

    /**
     * Boot function from Laravel model lifecycle.
     * Automatically generates UUID v4 for the primary key 'user_id' upon creation.
     *
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    // --- Relationships ---

    /**
     * One User has many Participations.
     *
     */
    public function participations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Participation::class, 'user_id', 'user_id');
    }

    /**
     * One User has many DeviceTokens.
     *
     */
    public function deviceTokens(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DeviceToken::class, 'user_id', 'user_id');
    }

    /**
     * Many-to-many relationship.
     *
     */
    public function participatingGames(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Game::class, 'participations', 'user_id', 'game_id')
                    ->withTimestamps(); 
    }

    // --- Pruning Logic ---

    /**
     * Get the prunable model query.
     * Defines which soft-deleted users should be permanently deleted.
     *
     */
    public function prunable(): \Illuminate\Database\Eloquent\Builder
    {
        // Target users soft-deleted more than 60 days ago
        return static::where('deleted_at', '<=', now()->subDays(60));
    }
}