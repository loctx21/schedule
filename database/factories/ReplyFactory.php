<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Reply;
use Faker\Generator as Faker;

$factory->define(Reply::class, function (Faker $faker) {
    return [
        'message' => $faker->sentence(10),
        'status' => Reply::STATUS_NOT_PUBLISH,
        'fb_target_id' => 'fb_target_id_string'
    ];
});

$factory->state(Reply::class, 'type_video', [
    'type' => Reply::TYPE_VIDEO
]);

$factory->state(Reply::class, 'type_visitor_post', [
    'type' => Reply::TYPE_VISITOR_POST
]);

$factory->state(Reply::class, 'type_photo_comment', [
    'type' => Reply::TYPE_PHOTO_COMMENT
]);

$factory->state(Reply::class, 'type_message', [
    'type' => Reply::TYPE_MESSAGE
]);