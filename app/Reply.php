<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Reply extends Model
{
    const STATUS_NOT_PUBLISH = 0;
    const STATUS_PUBLISH = 1;
    const STATUS_PROCESSING = 2;

    protected $fillable = ['message', 'page_id', 'post_id', 'fb_target_id', 'user_id', 
        'published_at'];

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
