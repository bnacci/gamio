<?php

// Check if the function trGetUser is already defined to avoid redeclaration.
if (! function_exists("trGetUser")) {
    /**
     * Retrieve the user by ID or the authenticated user.
     *
     * This function returns the authenticated user if no ID is provided.
     * If an ID is provided, it fetches the user from the database using the configured user model.
     *
     * @param  int|null $id The user ID to fetch. If null, the authenticated user is returned.
     * @return \Illuminate\Contracts\Auth\Authenticatable|null The user instance or null if not found.
     */
    function trGetUser($id = null)
    {
        // Return the authenticated user if no ID is provided
        if (auth()->check() && is_null($id)) {
            return auth()->user();
        }

        // If an ID is provided, return the user from the database using the configured user model
        if ($id) {
            return config("gamio.user_model")::find($id);
        }

        // Return null if no user is found or ID is invalid
        return null;
    }
}
