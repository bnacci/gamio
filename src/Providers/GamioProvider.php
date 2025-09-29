<?php
namespace Bnacci\Gamio\Providers;

use Bnacci\Gamio\Console\Commands\CreateBadgeCommand;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Class LevelSystemProvider
 *
 * Service provider for the LevelSystem package, handling migrations, views, routes, and Blade directives.
 */
class GamioProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * This method is used to register any services or bindings for the package.
     * It is typically where package-level functionality like dependency injections is configured.
     *
     * @return void
     */
    public function register(): void
    {
        $this->commands([
            CreateBadgeCommand::class,
        ]);
        // Register any package services or bindings here.
    }

    /**
     * Bootstrap services.
     *
     * This method is responsible for loading migrations, views, routes, configuration,
     * publishing migrations, config files, and setting up Blade directives.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->loadMigrations();
        $this->loadViews();
        $this->loadRoutes();
        $this->mergeConfig();
        $this->publishMigrations();
        $this->publishConfig();
        $this->publishViews();
        $this->bladeDirectives();
    }

    /**
     * Load migrations for the package.
     *
     * This method loads the migration files from the specified directory
     * so they can be run by Laravel's migration system.
     *
     * @return void
     */
    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . "/../../database/migrations");
    }

    /**
     * Load views for the package.
     *
     * This method loads the package's views so they can be used in the application.
     *
     * @return void
     */
    private function loadViews(): void
    {
        $this->loadViewsFrom(
            __DIR__ . "/../../resources/views",
            "gamio"
        );
    }

    /**
     * Load routes for the package.
     *
     * This method loads the package's routes from the specified file.
     *
     * @return void
     */
    private function loadRoutes(): void
    {
        $this->loadRoutesFrom(__DIR__ . "/../../routes/web.php");
    }

    /**
     * Merge the package configuration with the application's config.
     *
     * This method allows the package's configuration file to be merged with the app's config files,
     * ensuring it can be customized by the user.
     *
     * @return void
     */
    private function mergeConfig(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . "/../../config/gamio.php",
            "gamio"
        );
    }

    /**
     * Publish migrations for the package.
     *
     * This method publishes the package's migration files to the application's migration directory
     * so the user can modify them if necessary.
     *
     * @return void
     */
    private function publishMigrations(): void
    {
        $this->publishes(
            [
                __DIR__ . "/../../database/migrations" => database_path("migrations"),
            ],
            "gamio-migrations"
        );
    }

    /**
     * Publish configuration files for the package.
     *
     * This method publishes the package's configuration file to the application's config directory
     * so the user can modify the configuration values if needed.
     *
     * @return void
     */
    private function publishConfig(): void
    {
        $this->publishes(
            [
                __DIR__ . "/../../config/gamio.php" => config_path(
                    "gamio.php"
                ),
            ],
            "gamio-config"
        );
    }

    private function publishViews(): void
    {
        $this->publishes(
            [
                __DIR__ . "/../../resources/views" => resource_path(
                    "views/vendor/gamio"
                ),
            ],
            "gamio-views"
        );
    }

    /**
     * Register Blade directives for the package.
     *
     * This method defines custom Blade directives that can be used in views. These directives allow
     * the user to display elements like the leaderboard, user rank, XP, level, and max level check.
     *
     * @return void
     */
    private function bladeDirectives(): void
    {
        /**
         * Directive to render the leaderboard.
         *
         * @return string The Blade directive to display the leaderboard.
         */
        Blade::directive("leaderboard", function () {
            return "<?php echo view('gamio::leaderboard', [
                        'leaderboard' => config('gamio.user_model')::leaderboard(),
                        'from' => 'directive',
                    ])->render(); ?>";
        });

        /**
         * Directive to display the user's rank.
         *
         * @param  mixed $userId The user ID for which the rank is displayed.
         * @return string The Blade directive to display the user's rank.
         */
        Blade::directive("userRank", function ($userId) {
            return "<?php if (\$user = trGetUser($userId)) {
                        echo \$user->rank ? \$user->rank : 'Not eligible';}
                    ?>";
        });

        /**
         * Directive to display the user's XP.
         *
         * @param  mixed $userId The user ID for which the XP is displayed.
         * @return string The Blade directive to display the user's XP.
         */
        Blade::directive("userXp", function ($userId) {
            return "<?php if (\$user = trGetUser($userId)) {
                        echo \$user->xp;
                    } ?>";
        });

        /**
         * Directive to display the user's total XP with formatting.
         *
         * @param  mixed $userId The user ID for which the total XP is displayed.
         * @return string The Blade directive to display the user's total XP.
         */
        Blade::directive("userTotalXp", function ($userId) {
            return "<?php if (\$user = trGetUser($userId)) {
                        echo \Number::format(\$user->total_xp);
                    } ?>";
        });

        /**
         * Directive to display the user's level.
         *
         * @param  mixed $userId The user ID for which the level is displayed.
         * @return string The Blade directive to display the user's level.
         */
        Blade::directive("userLevel", function ($userId) {
            return "<?php if (\$user = trGetUser($userId)) {
                        echo \$user->level;
                    } ?>";
        });

        /**
         * Directive to check if a user has reached the maximum level.
         *
         * @param  mixed $expression The user ID or expression to check max level.
         * @return string The Blade directive to check if the user has max level.
         */
        Blade::directive("maxlevel", function ($expression) {
            if ($expression) {
                return "<?php if(trGetUser($expression) && trGetUser($expression)->progress->max_level): ?>";
            }

            return "<?php if(auth()->check() && auth()->user()->progress->max_level): ?>";
        });

        /**
         * Directive to end the max level check block.
         *
         * @return string The Blade directive to end the max level check.
         */
        Blade::directive("endmaxlevel", function () {
            return "<?php endif; ?>";
        });
    }
}
