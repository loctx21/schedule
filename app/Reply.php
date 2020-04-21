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

    /**
     * Extract Fb target id for reply
     * 
     * @param string $url
     * 
     * @return string
     */
    static function extractReplyTargetId($url) {
        if (empty($url))
            return null;
            
        $url = $url;
        $comp = parse_url(trim($url));
       
        //If url is videos return video id
        if (strpos($comp['path'], 'videos') !== false) {
            $pathArr = explode('/', $comp['path']);
            return $pathArr[3];
        }
        
        //If url is message get the inbox id
        if (array_key_exists('query', $comp) && strpos($comp['query'], 'selected_item_id') !== false)
        {
            parse_str($comp['query'], $queryArr);   
            return $queryArr['selected_item_id']; 
        }

        //If thread return sender name just return it
        if (!array_key_exists('query', $comp))
            return trim($url);

        parse_str($comp['query'], $arr);
        if (strpos($url, 'comment_id') !== false) {
            $pathArr = explode('/', $comp['path']);
            return $pathArr[count($pathArr)-2] . '_' . $arr['comment_id'];
        }

        return $arr['fbid'];
    }

    /**
     * Extract reply type from target url
     * 
     * @param string $url
     * 
     * @return string
     */
    static public function extractReplyType($url) {
        if (empty($url))
            return Reply::TYPE_VISITOR_POST;
        
        $comp = parse_url(trim($url));
 
        //If url is videos return video type
        if (strpos($comp['path'], 'videos') !== false) {
            return Reply::TYPE_VIDEO;
        }
       
        //If url is inbox url 
         if (array_key_exists('query', $comp) 
            && strpos($comp['query'], 'selected_item_id') !== false)
            return  Reply::TYPE_MESSAGE;

        //If thread return sender name just return type message. 
        //This is incase we still need to go back to use sender name
        if (!array_key_exists('query', $comp))
            return Reply::TYPE_MESSAGE;
        
        parse_str($comp['query'], $arr);
        
        if (strpos($url, 'comment_id') !== false) 
            return Reply::TYPE_PHOTO_COMMENT;
        return Reply::TYPE_VISITOR_POST;
    }
}
