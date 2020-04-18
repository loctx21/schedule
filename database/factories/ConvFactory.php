<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Conversation;
use Faker\Generator as Faker;

$factory->define(Conversation::class, function (Faker $faker) {
    return [
        'fb_id' => $faker->unique()->randomNumber(9),
        'fb_sender_id' => $faker->unique()->randomNumber(9),
        'fb_sender_name' => $faker->firstName()
    ];
});
