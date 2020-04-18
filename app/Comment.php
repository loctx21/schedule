<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    const STATUS_NOT_PUBLISH = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_PUBLISH_FAILED = 4;

    protected $fillable = ['message','published_at','user_id','post_id'];
}
