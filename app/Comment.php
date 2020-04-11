<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    const STATUS_NOT_PUBLISH = 0;
    const STATUS_PUBLISH = 1;
    const STATUS_DRAFT = 2;
    const STATUS_PROCESSING = 3;

    protected $fillable = ['message','published_at','user_id','post_id','page_id'];
}
