<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    const STATUS_NOT_PUBLISH = 0;
    const STATUS_PUBLISH = 1;
    const STATUS_DRAFT = 2;
    const STATUS_PROCESSING = 3;

    const TYPE_PHOTO_ID = 1;
    const TYPE_VIDEO_ID = 2;
    const TYPE_LINK_ID  = 3;

    const TYPE_PHOTO    = 'photo';
    const TYPE_PHOTO_ALBUM = 'album_photo';
    const TYPE_LINK     = 'link';
    const TYPE_VIDEO    = 'video';

    protected $fillable = ['message', 'user_id', 'page_id', 'fb_id', 'fb_album_id',
        'media_url', 'scheduled_at', 'link', 'type', 'video_title'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function page() {
        return $this->belongsTo(Page::class);
    }
    
    public function comments() {
        return $this->hasMany(Comment::class);
    }
    
    public function replies() {
        return $this->hasMany(Reply::class);
    }
}
