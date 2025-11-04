<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->uuid('notification_log_id')->primary();
            $table->enum('type', ['push', 'email'])->comment('通知種類: push=プッシュ通知, email=メール');
            $table->enum('target_type', ['all', 'game'])->comment('配信対象: all=全ユーザー, game=特定試合参加者');
            $table->uuid('game_id')->nullable()->comment('対象試合ID（target_type=gameの場合のみ）');
            $table->string('title', 200)->nullable()->comment('タイトル（プッシュ通知の場合）または件名（メールの場合）');
            $table->text('body')->comment('本文');
            $table->integer('sent_count')->default(0)->comment('送信成功件数');
            $table->integer('failed_count')->default(0)->comment('送信失敗件数');
            $table->string('sent_by_admin', 255)->nullable()->comment('送信者（管理者メールアドレス）');
            $table->enum('status', ['送信中', '送信完了', '送信失敗'])->default('送信中')->comment('送信ステータス');
            $table->text('error_message')->nullable()->comment('エラーメッセージ（送信失敗時）');
            $table->timestamps();

            // 外部キー制約
            $table->foreign('game_id')->references('game_id')->on('games')->onDelete('set null');
            
            // インデックス
            $table->index('type');
            $table->index('target_type');
            $table->index('created_at');
            $table->index('sent_by_admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};