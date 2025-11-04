<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'notification_logs';
    protected $primaryKey = 'notification_log_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'type',
        'target_type',
        'game_id',
        'title',
        'body',
        'sent_count',
        'failed_count',
        'sent_by_admin',
        'status',
        'error_message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 対象試合とのリレーション
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class, 'game_id', 'game_id');
    }
}