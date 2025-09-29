<?php

// use App\Gamio\Badges\OtakuBadge;
use Illuminate\Support\Facades\Route;

/**
 * Define the route for displaying the leaderboard.
 *
 * This route fetches the leaderboard data from the configured user model
 * and displays it using the "gamio::leaderboard-route" view.
 */
Route::get("leaderboard", function () {
    $leaderboard = config("gamio.user_model")::leaderboard();

    // Return the view with the leaderboard data and a flag indicating the source.
    return view("gamio::leaderboard-route", [
        "leaderboard" => $leaderboard, // The leaderboard data to be passed to the view
        "from"        => "route",      // A flag to identify the source of the request
    ]);
})
    ->name("leaderboard") // Name the route for easy referencing
    ->middleware("web");  // Apply the default 'web' middleware group
