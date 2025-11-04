<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // For UUID generation

class DeviceToken extends Model
{
    use HasFactory;

    // --- Table Configuration ---
    protected $table = 'device_tokens';
    protected $primaryKey = 'device_token_id'; // Define primary key
    public $incrementing = false; // Set to false for UUIDs
    protected $keyType = 'string'; // Set key type to string for UUIDs

    /**
     * The attributes that aren't mass assignable.
     * @var array<string>|bool
     */
    protected $guarded = []; // Allow mass assignment

    /**
     * The attributes that should be cast.
     * @var array<string, string>
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel model lifecycle.
     * Automatically generates UUID v4 for the primary key 'device_token_id' upon creation.
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
     * Get the user associated with this device token.
     * One DeviceToken belongs to one User.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Foreign key is 'user_id', owner key on 'users' table is 'user_id'
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}