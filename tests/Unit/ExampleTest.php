<?php

use Bnacci\Gamio\Providers\GamioProvider;

test('check if service provider exists', function () {
    expect(class_exists(GamioProvider::class))->toBeTrue();
});
