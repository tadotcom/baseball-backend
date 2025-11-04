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
        Schema::create('users', function (Blueprint $table) {
            // Columns based on 詳細３と４.txt
            $table->uuid('user_id')->primary()->comment('ユーザーID(UUID v4)'); //
            $table->string('email')->unique()->comment('メールアドレス'); //
            $table->string('password')->comment('パスワード(Bcryptハッシュ)'); //
            $table->string('nickname', 4)->unique()->comment('ニックネーム(固定4文字)'); //
            // $table->timestamp('email_verified_at')->nullable(); // If using email verification
            // $table->rememberToken(); // If using web sessions

            // Timestamps
            $table->timestamp('created_at')->useCurrent()->comment('登録日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');
            $table->softDeletes()->comment('退会日時(論理削除)'); // Adds deleted_at column

            // Optional: Indexing (Unique constraints are already indexed)
            // $table->index('deleted_at'); // Index for filtering soft deletes - Handled by softDeletes()

            // Table comment
            $table->comment('ユーザー');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};