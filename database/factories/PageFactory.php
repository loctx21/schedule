<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Page;
use Faker\Generator as Faker;

$factory->define(Page::class, function (Faker $faker) {
    return [
        'fb_id' => $faker->unique()->randomNumber(9),
        'name'  => $faker->company,
        'timezone' => 'America/Chicago'
    ];
});
