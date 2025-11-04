<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // For raw statements if needed
use Illuminate\Support\Facades\Log; // For logging warnings

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Define ENUM values
        $deviceTypes = ['ios', 'android'];

        Schema::create('device_tokens', function (Blueprint $table) use ($deviceTypes) {
            // Columns based on 詳細３と４.txt
            $table->uuid('device_token_id')->primary()->comment('デバイストークンID(UUID v4)'); //
            $table->foreignUuid('user_id')->comment('ユーザーID')->constrained('users', 'user_id')->onDelete('restrict')->onUpdate('cascade'); //
            $table->string('token')->unique()->comment('FCMトークン'); // Unique token
            $table->string('device_type', 10)->comment('ios or android'); //

            // Timestamps
            $table->timestamp('created_at')->useCurrent()->comment('登録日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');

            // --- Indexes ---
            // Unique constraint on 'token' is already indexed.
            // Index for 'user_id' (foreign key) is typically automatic.
            // $table->index('user_id'); // Explicitly add if needed

            // Table comment
            $table->comment('デバイストークン');
        });

         // Add CHECK constraints using DB::statement after table creation if needed
         if (Schema::hasTable('device_tokens')) {
             try {
                 if (DB::connection()->getDriverName() === 'mysql') {
                    DB::statement('ALTER TABLE device_tokens ADD CONSTRAINT chk_device_type CHECK (device_type IN ("'.implode('","', $deviceTypes).'"))');
                 }
             } catch (\Exception $e) {
                  Log::warning("Could not add CHECK constraints to 'device_tokens' table: " . $e->getMessage());
             }
         }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tokens');
    }
};