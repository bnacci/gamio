# Gamio Level System and Xp

I spent a lot of time trying to find a simple package that would provide me with an effective level and xp system and according to what I was looking for, I tried using some (they are good, but not for me) so I decided to create my own and from that desire Gamio was born.

## Features

- Level and XP
- Badges
- Leaderboard
- User rank

## Installation

IInstall the package via Composer:

```sh
  composer require bnacci/gamio
```

Publish the config file:

```sh
    php artisan vendor:publish --provider="Bnacci\Gamio\Providers\GamioProvider" --tag="gamio-config"
```

Publish the migration files:

```sh
    php artisan vendor:publish --provider="Bnacci\Gamio\Providers\GamioProvider" --tag="gamio-migrations"
```

Publish the view files:

```sh
    php artisan vendor:publish --provider="Bnacci\Gamio\Providers\GamioProvider" --tag="gamio-views"
```

Then just add Leveable Trait (Bnacci\Gamio\Traits\Leveable) to User Model:

```php
    <?php

    namespace App\Models;

    // use Illuminate\Contracts\Auth\MustVerifyEmail;
    use Illuminate\Database\Eloquent\Factories\HasFactory;
    use Illuminate\Foundation\Auth\User as Authenticatable;
    use Illuminate\Notifications\Notifiable;

    use Bnacci\Gamio\Traits\Leveable; // Leveable Trait Here

    class User extends Authenticatable
    {
        /** @use HasFactory<\Database\Factories\UserFactory> */
        use HasFactory, Notifiable;
        use Leveable; // Add this line to your model

        /**
        * The attributes that are mass assignable.
        *
        * @var list<string>
        */
        protected $fillable = [
            'name',
            'email',
            'password',
        ];

        /**
        * The attributes that should be hidden for serialization.
        *
        * @var list<string>
        */
        protected $hidden = [
            'password',
            'remember_token',
        ];

        /**
        * Get the attributes that should be cast.
        *
        * @return array<string, string>
        */
        protected function casts(): array
        {
            return [
                'email_verified_at' => 'datetime',
                'password' => 'hashed',
            ];
        }
    }
```

## Migrations

Migrations

Gamio adds new columns to your users table (xp, level, coins, eligible).
Run the migrations:

```sh
php artisan migrate
```

## Configuration

The config file is located at config/gamio.php.
Here you can customize:

```php
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
```

## Usage

You can now use the new methods on your User model.

Add xp to user:

```php
    <?php

    $user = User::find(1);
    $user->addXp(50);

    echo $user->xp;     // 50
    echo $user->level;  // 1 (The initial level is 1 and the maximum level depends on the configuration)
```

Make the user eligible to receive experience points:

```php
    <?php

    $user = User::find(1);
    $user->eligible(false); // default is true
```

Get user progress:

```php
    <?php

    $user = User::find(1);
    $user->addXp(300);
    return $user->progress;
```

User progress return a json object like:

```json
{
  "level": 3, // Current level
  "xp": 36, // Current xp for current level
  "next_level_xp": 172, // XP for the next level
  "next_level": 10, // Next level
  "progress": 20.93, // Current level progress
  "max_level": false // If the user has the maximum level
}
```

Reset user progress:

```php
    <?php

    $user = User::find(1);
    $user->reset();
```

Get user rank:

```php
 <?php

    $user = User::find(1);
    return $user->rank; // Return user rank or use @userRank directive
```

Get the leaderboard for all users using leaderboard() method or @leaderboard directive or accessing the /leaderboard route (publish the views to update the leaderboard view)

```php
 <?php

    $user = User::leaderboard(); // Returns all users sorted by rank
```

Get user badges:

```php
 <?php

    $user = User::find(1);
    return $user->badges;
```

Note: The level will be generated automatically based on the amount of XP, so there is no method to add levels.

## Badges

To create badges Gamio has an artisan command to make this easier for you, just run:

```sh
    php artisan gamio:badge

    What's badge name?:
    > Gamio

    What's level user gain this badge?:
    > 10

    Do you want to create this badge? (yes/no) [yes]:
    > yes
```

The badge will be created in: <your_project_folder>\App\Gamio\Badges\GamioBadge.php

All badges will have the suffix "Badge" and when creating a new badge Gamio will create the file Badges/UserBadges.php:

```php
    <?php

    namespace App\Gamio\Badges;

    class UserBadges
    {
        // This function returns all the badges you have created.
        public function getData()
        {
            return [
                "gamio" => \App\Gamio\Badges\GamioBadge::class, // The created badge class
                // ...other badges
            ];
        }
    }

```

The badge file will contain the following code:

```php
    <?php

    namespace App\Gamio\Badges;

    // Import the default Gamio Badge
    use Bnacci\Gamio\Badge;

    class TesteBadge extends Badge
    {
        protected $gainIn = 90; // Level you set when creating the badge. If needed, you can change it here
        protected $id = "gamio"; // The ID is generated based on your name. If you need a new ID, change it here.
        protected $name = "Gamio"; // The name you set when creating the badge.  If needed, you can change it here

        // If your badge has an icon, put its URL here.
        public function icon() : string | null {
            return null;
        }
    }

```

How to use badges:

You won't need to do anything to use them because as soon as you create them, it generates UserBadges.php containing all your badges, and from there, Gamio takes over the role of awarding the badge according to the user's level. Just create it and you're good to go 😉.

## Contributing

Thank you for considering contributing to Gamio!
Fork the repo, create a branch, make your changes, and submit a PR.

## License

This package is open-sourced software licensed under the [MIT license](https://choosealicense.com/licenses/mit/).
