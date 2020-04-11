<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'message' => $faker->sentence(10)
    ];
});

$factory->state(Post::class, 'type_photo_url', [
    'media_url' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png',
    'type' => Post::TYPE_PHOTO_ID,
    'status' => Post::STATUS_NOT_PUBLISH
]);
