<?php

use App\Models\User;
use Illuminate\Support\Facades\Schema;

describe('XP and Level System', function () {
    /**
     * Ensure that the "users" table contains the "xp" column.
     */
    it('checks if column xp exists on users table', function () {
        expect(Schema::hasColumn('users', 'xp'))->toBeTrue();
    });

    /**
     * Ensure that a new user is created with the default XP value (0).
     */
    it('checks user default xp value', function () {
        $user = User::create([
            "name"     => "Teste",
            "email"    => "test@test.com",
            "password" => bcrypt("secret"),
        ])->fresh();

        expect($user->xp)->toBe(0);
    });

    /**
     * Ensure that XP can be incremented for a user
     * and the value is persisted correctly.
     */
    it('checks user xp increment', function () {
        $user = User::create([
            "name"     => "Teste",
            "email"    => "test2@test.com",
            "password" => bcrypt("secret"),
        ])->fresh();

        $user->addXp(10);

        expect($user->xp)->toBe(10);
    });

    /**
     * Ensure that when XP is enough,
     * the user levels up correctly.
     */
    it('checks user level up to 2', function () {
        $user = User::create([
            "name"     => "Teste",
            "email"    => "test3@test.com",
            "password" => bcrypt("secret"),
        ])->fresh();

        $user->addXp(200);

        expect($user->level)->toBe(2);
    });

    /**
     * Ensure that the user "eligible" field
     * can be updated and stored properly.
     */
    it('checks user is eligible', function () {
        $user = User::create([
            "name"     => "Teste",
            "email"    => "test4@test.com",
            "password" => bcrypt("secret"),
        ])->fresh();

        $user->update(["eligible" => true]);

        expect($user->eligible)->toBeTrue();
    });

    /**
     * Ensure that a user starts with the default amount of coins
     * defined in the "gamio.initial_coins" config.
     */
    it('checks default coins for user', function () {
        $user = User::create([
            "name"     => "Teste",
            "email"    => "test5@test.com",
            "password" => bcrypt("secret"),
        ])->fresh();

        expect($user->coins)->toBe(config("gamio.initial_coins"));
    });
});
