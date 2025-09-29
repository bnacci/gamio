<?php
namespace Bnacci\Gamio\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Class Badge
 *
 * Represents the association between a user and a badge. This model is used to store information
 * about which user has earned which badge.
 */
class Badge extends Model
{
    // The attributes that are mass assignable.
    protected $fillable = ["user_id", "badge_id", "badge_icon", "won_in", "badge_name"];

                                   // Define the type of the primary key.
    protected $keyType = "string"; // The user_badge ID is a string type, not an auto-incrementing integer.

    // Disable auto-incrementing for the primary key.
    public $incrementing = false;

    // The attributes that should be hidden for arrays (e.g., in JSON responses).
    protected $hidden = ["id"];

    protected $table = "user_badges";

    /**
     * Boot the model and perform actions when a new user badge is being created.
     *
     * This method automatically generates a unique UUID for the user badge ID
     * before the user badge record is created.
     */
    public static function booted()
    {
        // Register a "creating" event listener for the Badge model.
        static::creating(function ($model) {
            // Generate a unique UUID for the user badge ID.
            $model->id = Str::uuid();
        });
    }
}
