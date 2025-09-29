<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This method creates the `user_badges` table.
     * The table tracks which badges have been awarded to users.
     */
    public function up(): void
    {
        Schema::create("user_badges", function (Blueprint $table) {
            // The 'id' column is a UUID and is the primary key for this table
            $table->uuid("id")->primary();

            // The 'user_id' column is a foreign key that references the 'id' column in the 'users' table
            $table
                ->foreignId("user_id") // Creates a foreign key for 'user_id'
                ->constrained("users") // References the 'users' table
                ->onDelete("cascade"); // Deletes related 'user_badge' records when the user is deleted

            $table->string("badge_id")->unique();
            $table->string("badge_name");
            $table->string("badge_icon")->nullable();
            $table->integer("won_in");

            // Adds timestamp columns: 'created_at' and 'updated_at'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method is responsible for removing the `user_badges` table.
     * It is used to undo the migration if needed.
     */
    public function down(): void
    {
        // Drops the 'user_badges' table if it exists
        Schema::dropIfExists("user_badges");
    }
};
