<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // For UUID generation

class Participation extends Model
{
    // Use HasFactory trait if you plan to create factories for testing
    use HasFactory;

    // --- Table Configuration ---
    protected $table = 'participations';
    protected $primaryKey = 'participation_id'; // Define primary key
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
     * Automatically generates UUID v4 for the primary key 'participation_id' upon creation.
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
     * Get the user associated with this participation.
     * One Participation belongs to one User.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Foreign key is 'user_id', owner key on 'users' table is 'user_id'
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Get the game associated with this participation.
     * One Participation belongs to one Game.
     */
    public function game(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        // Foreign key is 'game_id', owner key on 'games' table is 'game_id'
        return $this->belongsTo(Game::class, 'game_id', 'game_id');
    }
}