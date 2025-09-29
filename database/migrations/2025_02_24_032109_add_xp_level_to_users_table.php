<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method is responsible for adding new columns to the `users` table.
     * These columns are used to manage user XP, level, total XP, and eligibility status for leaderboard participation.
     */
    public function up(): void
    {
        Schema::table("users", function (Blueprint $table) {
            // Add the 'xp' column (unsigned big integer) with a default value of 0
            $table->unsignedBigInteger("xp")->default(0);

            // Add the 'level' column (unsigned integer) with a default value of 1
            $table->unsignedInteger("level")->default(1);

            // Add the 'total_xp' column (unsigned big integer) with a default value of 0
            $table->unsignedBigInteger("total_xp")->default(0);

            // Add the 'eligible' column (boolean) with a default value of true
            $table->boolean("eligible")->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method is responsible for removing the columns added in the `up` method.
     * It is used to undo the migration if needed.
     */
    public function down(): void
    {
        Schema::table("users", function (Blueprint $table) {
            // Drop the 'xp', 'level', 'total_xp', and 'eligible' columns
            $table->dropColumn(["xp", "level", "total_xp", "eligible"]);
        });
    }
};
