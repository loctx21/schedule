<?php

namespace App;

use App\Helper\Utils;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Post extends Model
{
    const STATUS_NOT_PUBLISH = 0;
    const STATUS_PUBLISHED = 1;
    const STATUS_DRAFT = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_PUBLISH_FAILED = 4;

    const STATUS_TEXT = [
        self::STATUS_NOT_PUBLISH => 'not publish',
        self::STATUS_PUBLISHED => 'published',
        self::STATUS_DRAFT => 'draft',
        self::STATUS_PROCESSING => 'processing',
        self::STATUS_PUBLISH_FAILED => 'publish failed',
    ];

    const TYPE_PHOTO_ID = 1;
    const TYPE_VIDEO_ID = 2;
    const TYPE_LINK_ID  = 3;
    const TYPE_PHOTO_ALBUM_ID = 4;

    const TYPE_PHOTO    = 'photo';
    const TYPE_PHOTO_ALBUM = 'album_photo';
    const TYPE_LINK     = 'link';
    const TYPE_VIDEO    = 'video';

    const TYPE_TEXT = [
        self::TYPE_PHOTO_ID => self::TYPE_PHOTO,
        self::TYPE_VIDEO_ID => self::TYPE_VIDEO,
        self::TYPE_LINK_ID => self::TYPE_LINK,
        self::TYPE_PHOTO_ALBUM_ID => self::TYPE_PHOTO
    ];

    protected $fillable = ['message', 'user_id', 'page_id', 'fb_id', 'fb_album_id',
        'media_url', 'scheduled_at', 'link', 'type', 'video_title', 'target_url'];

    protected $appends = ['status_text', 'type_text', 'fb_post_link'];

    public function user() {
        return $this->belongsTo(User::class);
    }
    
    public function page() {
        return $this->belongsTo(Page::class);
    }

    public function comment() {
        return $this->hasOne(Comment::class);
    }
    
    public function reply() {
        return $this->hasOne(Reply::class);
    }
    
    public function comments() {
        return $this->hasMany(Comment::class);
    }
    
    public function replies() {
        return $this->hasMany(Reply::class);
    }

    public function getMediaUrlAttribute()
    {
        $media_url = $this->getOriginal('media_url');
        
        if (strpos($media_url, "://") !== false)
            return $media_url;

        return Storage::url($media_url);
    }

    /**
     * Get right Facebook object for related action
     * 
     * @return Integer
     */
    public function getFbObjectId() 
    {
        if (!empty($this->fb_post_id))
            return $this->fb_post_id;
        else
            return $this->fb_id;
    }

    /**
     * Get Facebook Post link
     * 
     * @return string
     */
    public function getFbPostLink() 
    {
        if (!empty($this->fb_post_id))
            return 'http://facebook.com/' . $this->fb_post_id;
        else
            return 'http://facebook.com/' . $this->fb_id;
    }

    public function getScheduledAtTimezone($timezone)
    {
        $date = new Carbon($this->scheduled_at);
        $date->setTimezone($timezone);
        return $date->format(Utils::DATETIMEFORMAT);
    }

    public function getFbPostLinkAttribute() 
    {
        if ($this->status != SELF::STATUS_PUBLISHED)
            return null;
            
        if (!empty($this->fb_post_id))
            return 'http://facebook.com/' . $this->fb_post_id;
        else
            return 'http://facebook.com/' . $this->fb_id;
    }

    public function getStatusTextAttribute()
    {
        if (is_null($this->status) || $this->status == "")
            return "";
        
        if (array_key_exists($this->status, self::STATUS_TEXT))
            return self::STATUS_TEXT[$this->status];

        return "";
    }

    public function getTypeTextAttribute()
    {
        if (is_null($this->type) || $this->type == "")
            return "";
        
        if (array_key_exists($this->type, self::TYPE_TEXT))
            return self::TYPE_TEXT[$this->type];

        return "";
    }
}
