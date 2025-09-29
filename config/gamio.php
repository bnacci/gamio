<?php

return [
    /**
     * The maximum level a user can achieve in the level system.
     * This defines the highest level in the system, users cannot level up beyond this.
     */
    "max_level"         => 100,

    /**
     * The user model to be used by the level system.
     * This specifies the fully qualified class name of the model that will be used
     * to manage users within the system. By default, it's set to the User model in the app.
     */
    "user_model"        => \App\Models\User::class,

    /**
     * The limit on how many users should be displayed in the leaderboard.
     * This determines how many top users are shown in the leaderboard at a time.
     * The default value is set to 10.
     */
    "leaderboard_limit" => 10,
];
