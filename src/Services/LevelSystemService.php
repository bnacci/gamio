<?php
namespace Bnacci\Gamio\Services;

use Bnacci\Gamio\App\Models\Badge;

/**
 * Class LevelSystemService
 *
 * Handles the logic related to user leveling, XP management, badges, and ranking.
 */
class LevelSystemService
{
    /**
     * Create a new LevelSystemService instance.
     *
     * @param $user
     */
    public function __construct(private $user)
    {
        // Constructor to initialize the service with the given user.
    }

    /**
     * Award badges to the user based on their level.
     *
     * This method checks all available badges and grants them to the user when their level
     * exceeds the threshold specified by the badge configuration.
     *
     * @param int $level
     * @return void
     */
    private function gainBadge(int $level): void
    {
        if (class_exists(\App\Gamio\Badges\UserBadges::class)) {
            foreach ((new \App\Gamio\Badges\UserBadges())->getData() as $key => $value) {
                $badge = (new $value);

                if (! Badge::where("user_id", $this->user->id)
                    ->where("badge_id", $badge->getId())
                    ->exists()) {

                    if ($badge->getGainIn() !== 0 && $level >= $badge->getGainIn()) {
                        Badge::create([
                            "user_id"    => $this->user->id,
                            "badge_id"   => $badge->getId(),
                            "badge_name" => $badge->getName(),
                            "won_in"     => $badge->getGainIn(),
                            "badge_icon" => $badge->icon(),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Add experience points (XP) to the user and handle level-up logic.
     *
     * This method adds XP to the user and checks whether the user should level up.
     * If the user's XP surpasses the required amount for the next level,
     * the user's level will increase, and any corresponding badges will be awarded.
     *
     * @param int $xp
     * @return void
     */
    public function addXp(int $xp): void
    {
        // Ensure the user is eligible and hasn't reached the max level
        if (
            ! $this->user->eligible ||
            $this->user->level >= config("gamio.max_level")
        ) {
            return;
        }

        // Add XP to the user's current and total XP
        $this->user->xp += $xp;
        $this->user->total_xp += $xp;

        // Loop through to check if user levels up based on XP thresholds
        while (
            $this->user->xp >= $this->getNextLevelXp() &&
            $this->user->level < config("gamio.max_level")
        ) {
            // Deduct XP for the current level and increase the level
            $this->user->xp -= $this->getNextLevelXp();
            $this->user->level++;

            // If max level is reached, reset the XP
            if ($this->user->level >= config("gamio.max_level")) {
                $this->user->xp = 0;
                break;
            }
        }

        // Award any badges corresponding to the new level
        $this->gainBadge($this->user->level);
        $this->user->save();
    }

    /**
     * Calculate the level based on the total XP.
     *
     * This method calculates the user's level based on their XP using a logarithmic formula.
     *
     * @param int $xp
     * @return int
     */
    private function calculateLevel(int $xp): int
    {
        // Use logarithmic progression to calculate the level
        $level = floor(pow($xp / 100, 1 / 1.2));
        return max(1, $level); // Ensure the level is at least 1
    }

    /**
     * Get the XP required for the next level.
     *
     * This method calculates the amount of XP needed for the user to reach the next level.
     *
     * @return int
     */
    private function getNextLevelXp(): int
    {
        // Use exponential formula to calculate the XP for the next level
        return floor(100 * pow(1.2, $this->user->level));
    }

    /**
     * Get the user's progress towards the next level.
     *
     * This method provides the user's current level, XP, progress towards the next level,
     * and whether the user has reached the max level.
     *
     * @return object
     */
    public function getProgress(): object
    {
        $currentXp   = $this->user->xp;
        $nextLevelXp = $this->getNextLevelXp();

        // Return the progress as a JSON object
        return json_decode(
            collect([
                "level"         => $this->user->level,
                "xp"            => $currentXp,
                "next_level_xp" => $nextLevelXp,
                "next_level"    => $this->user->level + 1,
                "progress"      => round(($currentXp / $nextLevelXp) * 100, 2),
                "max_level"     =>
                $this->user->level === config("gamio.max_level"),
            ])
        );
    }

    /**
     * Calculate and return the user's rank in the global leaderboard.
     *
     * The rank is determined based on multiple criteria applied in the following order:
     * - Users with a higher level rank above;
     * - If levels are tied, users with higher total_xp rank above;
     * - If total_xp is also tied, users with higher xp rank above;
     * - If xp is also tied, users with a name that is alphabetically smaller rank above;
     * - Finally, if all above are tied, users with an earlier created_at timestamp rank above.
     *
     * Only users marked as eligible (eligible = true) are considered.
     *
     * @return int The user's rank position (1 being the highest).
     */
    public function getUserRank(): int
    {
        $user = $this->user;

        // Count how many users rank above the current user
        $rank = config("gamio.user_model")::where("eligible", true)
            ->where(function ($query) use ($user) {
                $query
                    ->where("level", ">", $user->level)
                    ->orWhere(function ($q) use ($user) {
                        $q->where("level", $user->level)
                            ->where("total_xp", ">", $user->total_xp);
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where("level", $user->level)
                            ->where("total_xp", $user->total_xp)
                            ->where("xp", ">", $user->xp);
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where("level", $user->level)
                            ->where("total_xp", $user->total_xp)
                            ->where("xp", $user->xp)
                            ->where("name", "<", $user->name);
                    })
                    ->orWhere(function ($q) use ($user) {
                        $q->where("level", $user->level)
                            ->where("total_xp", $user->total_xp)
                            ->where("xp", $user->xp)
                            ->where("name", $user->name)
                            ->where("created_at", "<", $user->created_at);
                    });
            })
            ->count();

        // User's rank is the count of users above + 1
        return $rank + 1;
    }

    /**
     * Reset the user's level, XP, and badges.
     *
     * This method resets the user's level and XP to 1 and 0, respectively, and removes
     * any associated badges from the user.
     *
     * @return bool
     */
    public function reset(): bool
    {
        $this->user->level    = 1;
        $this->user->xp       = 0;
        $this->user->total_xp = 0;

        // Save the user's progress and delete their badges
        if ($this->user->save()) {
            Badge::where("user_id", $this->user->id)->delete();
            return true;
        }

        return false; // Return false if the save failed
    }

    /**
     * Set the user's eligibility for the leaderboard.
     *
     * This method updates whether the user is eligible for ranking in the leaderboard.
     *
     * @param bool $isEligible
     * @return void
     */
    public function eligible(bool $isEligible): void
    {
        $this->user->eligible = $isEligible;
        $this->user->save();
    }
}
