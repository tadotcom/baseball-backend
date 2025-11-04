<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str; // For UUID generation

class Game extends Model
{
    use HasFactory;

    // --- Table Configuration ---
    protected $table = 'games';
    protected $primaryKey = 'game_id'; // Define primary key
    public $incrementing = false; // Set to false for UUIDs
    protected $keyType = 'string'; // Set key type to string for UUIDs

    /**
     * The attributes that aren't mass assignable.
     * Use guarded instead of fillable for convenience if most fields are assignable.
     * Empty array means all attributes are mass assignable.
     * @var array<string>|bool
     */
    protected $guarded = []; // Allow mass assignment for all fields defined in fillable during create/update

    /**
     * The attributes that should be cast.
     * Ensure correct data types.
     * @var array<string, string>
     */
    protected $casts = [
        'game_date_time' => 'datetime', // Cast to Carbon instance
        'latitude' => 'float', //
        'longitude' => 'float',//
        'acceptable_radius' => 'integer', //
        'fee' => 'integer', //
        'capacity' => 'integer', //
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot function from Laravel model lifecycle.
     * Automatically generates UUID v4 for the primary key 'game_id' upon creation.
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
     * Get the participations associated with the game.
     * One Game has many Participations.
     */
    public function participations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        // Foreign key in 'participations' table is 'game_id', local key in 'games' table is 'game_id'
        return $this->hasMany(Participation::class, 'game_id', 'game_id');
    }

    /**
     * Get the users participating in the game (through participations).
     * Many-to-many relationship.
     */
    public function participants(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        // Pivot table is 'participations', foreign key is 'game_id', related key is 'user_id'
        return $this->belongsToMany(User::class, 'participations', 'game_id', 'user_id')
                    // Include pivot table columns if needed
                    ->withPivot('participation_id', 'team_division', 'position', 'status')
                    ->withTimestamps(); // Include created_at/updated_at from pivot table
    }

    // --- Scopes (Optional) ---
    /**
     * Scope a query to only include active games (募集中 or 満員).
     */
    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->whereIn('status', ['募集中', '満員']);
    }

     /**
     * Scope a query to only include upcoming games.
     */
    public function scopeUpcoming(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        // Consider games starting within the next hour or later
        return $query->where('game_date_time', '>', now()->subHour());
    }

}