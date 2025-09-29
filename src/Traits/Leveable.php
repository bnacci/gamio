<?php
namespace Bnacci\Gamio\Traits;

use Bnacci\Gamio\App\Models\Badge;
use Bnacci\Gamio\Services\LevelSystemService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Trait Leveable
 *
 * Adds leveling, XP, and badge management functionality to a model.
 */
trait Leveable
{
    public static function bootHasRank()
    {
        static::addGlobalScope('appendsRank', function ($builder) {
            // Verifica se já existe o atributo 'rank' no array $appends
            if (! in_array('rank', $builder->getModel()->getAppends())) {
                $builder->getModel()->appends[] = 'rank';
            }
        });
    }

    /**
     * Get the instance of LevelSystemService associated with the user.
     *
     * This method initializes and returns the LevelSystemService to handle all
     * leveling, XP, and related logic for the user.
     *
     * @return LevelSystemService
     */
    private function service(): LevelSystemService
    {
        return new LevelSystemService($this);
    }

    /**
     * Add experience points (XP) to the user and handle level-up logic.
     *
     * This method adds XP to the user and checks if the user should level up.
     *
     * @param int $xp The amount of XP to add.
     * @return void
     */
    public function addXp(int $xp): void
    {
        $this->service()->addXp($xp);
    }

    /**
     * Get the user's current rank based on XP and level.
     *
     * This method retrieves the rank of the user relative to others in the system.
     *
     * @return int The user's rank.
     */
    public function getRankAttribute(): int
    {
        return $this->service()->getUserRank();
    }

    /**
     * Retrieve a leaderboard of eligible users.
     *
     * This method fetches users eligible for ranking, ordered by level, total XP,
     * and XP, with a configurable limit on the number of users returned.
     *
     * @return Collection A collection of eligible users.
     */
    public static function leaderboard(): Collection
    {
        $users = config("gamio.user_model")::where("eligible", true)
            ->orderByDesc("level")
            ->orderByDesc("total_xp")
            ->orderByDesc("xp")
            ->orderBy("name")
            ->take(config("gamio.leaderboard_limit"))
            ->get();

        $users = $users->sortBy('rank')->values();

        return $users;

    }

    /**
     * Get the user's progress toward the next level as a percentage.
     *
     * This method calculates and returns the user's progress using LevelSystemService.
     *
     * @return float The progress percentage.
     */
    public function getProgressAttribute()
    {
        return $this->service()->getProgress();
    }

    /**
     * Reset the user's level, XP, and badges.
     *
     * This method resets the user's progress, starting them from level 1 with 0 XP,
     * and removes associated badges.
     *
     * @return bool True if reset is successful.
     */
    public function reset(): bool
    {
        return $this->service()->reset();
    }

    /**
     * Set the user's eligibility for the leaderboard.
     *
     * This method updates whether the user is eligible to appear in the leaderboard.
     *
     * @param bool $isEligible If true, the user will be included in the leaderboard.
     * @return void
     */
    public function makeEligible(bool $isEligible): void
    {
        $this->service()->eligible($isEligible);
    }

    /**
     * Get the badges associated with the user.
     *
     * This method defines a relationship with the Badge model, allowing the retrieval
     * of the badges the user has earned.
     *
     * @return HasMany Relationship with the Badge model.
     */
    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class, "user_id");
    }

    public function addCoins(int $amount)
    {
        $this->coins += $amount;
        $this->save();
    }

    public function removeCoins(int $amount)
    {
        if ($this->coins >= $amount) {
            $this->coins -= $amount;
            $this->save();
            return true;
        }
        return false; // Saldo insuficiente
    }
}
