<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    const STATUS_NOT_PUBLISH = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_PUBLISH_FAILED = 4;

    const TYPE_VISITOR_POST     = 0;
    const TYPE_PHOTO_COMMENT    = 1;
    const TYPE_MESSAGE          = 2;
    const TYPE_VIDEO            = 3; 

    protected $fillable = ['message', 'page_id', 'post_id', 'fb_target_id', 'user_id', 
        'published_at', 'type'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function page() {
        return $this->belongsTo(Page::class);
    }
    
    public function post() {
        return $this->belongsTo(Post::class);
    }
}
