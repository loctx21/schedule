<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        'message' => $faker->sentence(10),
        'status' => Post::STATUS_NOT_PUBLISH,
        'type' => Post::TYPE_LINK_ID
    ];
});

$factory->state(Post::class, 'type_photo_url', [
    'media_url' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_92x30dp.png',
    'type' => Post::TYPE_PHOTO_ID
]);

$factory->state(Post::class, 'type_link', [
    'link' => 'https://www.google.com',
    'type' => Post::TYPE_LINK_ID,
]);


$factory->state(Post::class, 'type_video', [
    'media_url' => 'https://www.google.com/video.mp4',
    'type' => Post::TYPE_VIDEO_ID,
    'video_title' => "This is video title"
]);
