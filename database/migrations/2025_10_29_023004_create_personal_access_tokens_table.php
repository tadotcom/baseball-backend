<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
//    public function up(): void
//    {
//        Schema::create('personal_access_tokens', function (Blueprint $table) {
//            $table->id();
//            $table->morphs('tokenable');
//            $table->text('name');
//            $table->string('token', 64)->unique();
//            $table->text('abilities')->nullable();
//            $table->timestamp('last_used_at')->nullable();
//            $table->timestamp('expires_at')->nullable()->index();
//            $table->timestamps();
//        });
//    }


public function up(): void
{
    Schema::create('personal_access_tokens', function (Blueprint $table) {
        $table->id();

        // ★★★ "morphs" を以下の3行に変更 ★★★
        $table->uuid('tokenable_id'); // UUID型に変更
        $table->string('tokenable_type');
        $table->index(['tokenable_id', 'tokenable_type']); // インデックスを手動で追加
        // ★★★ 変更ここまで ★★★

        $table->string('name');
        $table->string('token', 64)->unique();
        $table->text('abilities')->nullable();
        $table->timestamp('last_used_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
