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
        $teamDivisions = ['チームA', 'チームB'];
        $positions = ['投手', '捕手', '一塁手', '二塁手', '三塁手', '遊撃手', '左翼手', '中堅手', '右翼手'];
        $statuses = ['参加確定', 'チェックイン済'];

        Schema::create('participations', function (Blueprint $table) use ($teamDivisions, $positions, $statuses) {
            // Columns based on 詳細３と４.txt
            $table->uuid('participation_id')->primary()->comment('参加情報ID(UUID v4)'); //
            // Foreign keys
            $table->foreignUuid('user_id')->comment('ユーザーID')->constrained('users', 'user_id')->onDelete('restrict')->onUpdate('cascade'); //
            $table->foreignUuid('game_id')->comment('試合ID')->constrained('games', 'game_id')->onDelete('restrict')->onUpdate('cascade'); //

            // String columns acting as ENUMs
            $table->string('team_division', 20)->comment('チーム区分'); //
            $table->string('position', 20)->comment('守備ポジション'); //
            $table->string('status', 20)->default('参加確定')->comment('参加ステータス'); //

            // Timestamps
            $table->timestamp('created_at')->useCurrent()->comment('登録日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');

            // --- Indexes and Constraints ---
            $table->unique(['user_id', 'game_id']); // Prevent duplicate participation
            // Indexes for foreign keys are typically created automatically
            $table->index('status'); // Index for status filtering

            // Table comment
            $table->comment('参加情報');
        });

         // Add CHECK constraints using DB::statement after table creation if needed
         if (Schema::hasTable('participations')) {
             try {
                 if (DB::connection()->getDriverName() === 'mysql') {
                     DB::statement('ALTER TABLE participations ADD CONSTRAINT chk_participation_team CHECK (team_division IN ("'.implode('","', $teamDivisions).'"))');
                     DB::statement('ALTER TABLE participations ADD CONSTRAINT chk_participation_position CHECK (position IN ("'.implode('","', $positions).'"))');
                     DB::statement('ALTER TABLE participations ADD CONSTRAINT chk_participation_status CHECK (status IN ("'.implode('","', $statuses).'"))');
                 }
             } catch (\Exception $e) {
                  Log::warning("Could not add CHECK constraints to 'participations' table: " . $e->getMessage());
             }
         }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participations');
    }
};