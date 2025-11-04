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
         // Prefectures based on fixed rule
         $prefectures = ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'];
         // Game statuses based on fixed rule
         $statuses = ['募集中', '満員', '開催済み', '中止'];

        Schema::create('games', function (Blueprint $table) use ($prefectures, $statuses) {
            // Columns based on 詳細３と４.txt
            $table->uuid('game_id')->primary()->comment('試合ID(UUID v4)'); //
            $table->string('place_name', 254)->comment('場所名'); //
            $table->dateTime('game_date_time')->comment('開催日時'); //
            $table->string('address', 254)->comment('住所'); //
            // Use enum for prefecture if DB supports it, otherwise string with check constraint
            // $table->enum('prefecture', $prefectures)->comment('都道府県');
            $table->string('prefecture', 10)->comment('都道府県'); //
            $table->decimal('latitude', 10, 8)->comment('緯度'); // Precision, Scale
            $table->decimal('longitude', 11, 8)->comment('経度'); // Precision, Scale
            $table->unsignedInteger('acceptable_radius')->comment('許容半径(メートル)'); //
            // Use enum for status or string with check constraint
            // $table->enum('status', $statuses)->default('募集中')->comment('ステータス');
            $table->string('status', 20)->default('募集中')->comment('ステータス'); //
            $table->unsignedInteger('fee')->nullable()->default(null)->comment('参加費(円)'); // Nullable
            $table->unsignedInteger('capacity')->comment('募集人数'); //

            // Timestamps
            $table->timestamp('created_at')->useCurrent()->comment('登録日時');
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->comment('更新日時');

            // --- Indexes ---
            $table->index('game_date_time');
            $table->index('prefecture');
            $table->index('status');
            $table->index(['status', 'game_date_time']); // Composite index

            // Table comment
            $table->comment('試合');

             // --- CHECK Constraints (for string columns acting as ENUMs) ---
             // Note: CHECK constraints might require specific DB versions/configurations
             // DB::statement('ALTER TABLE games ADD CONSTRAINT chk_game_prefecture CHECK (prefecture IN ("'.implode('","', $prefectures).'"))');
             // DB::statement('ALTER TABLE games ADD CONSTRAINT chk_game_status CHECK (status IN ("'.implode('","', $statuses).'"))');
             // Consider using native ENUM type if possible and preferred. Laravel 10+ supports native enums.
        });

         // Add CHECK constraints using DB::statement after table creation if needed
         if (Schema::hasTable('games')) {
            try {
                // Raw statements are DB-specific
                if (DB::connection()->getDriverName() === 'mysql') {
                    DB::statement('ALTER TABLE games ADD CONSTRAINT chk_game_prefecture CHECK (prefecture IN ("'.implode('","', $prefectures).'"))');
                    DB::statement('ALTER TABLE games ADD CONSTRAINT chk_game_status CHECK (status IN ("'.implode('","', $statuses).'"))');
                }
                // Add similar statements for other supported DBs if necessary
            } catch (\Exception $e) {
                // Log or handle the error if constraints cannot be added (e.g., older DB versions)
                 Log::warning("Could not add CHECK constraints to 'games' table: " . $e->getMessage());
            }
         }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};