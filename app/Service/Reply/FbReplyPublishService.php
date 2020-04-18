<?php

namespace App\Service\Reply;

use App\Conversation;
use App\Post;
use App\Reply;
use App\Service\Page\PageFacebookService;
use Carbon\Carbon;

class FbReplyPublishService {

    /**
     * The post to publish
     * 
     * @var \App\Post
     */
    protected $post;

    /**
     * The comment to publish
     * 
     * @var \App\Reply
     */
    protected $reply;
    
    /**
     * Facecbook service object from SDK
     * 
     * @var Facebook\Facebook;
     */
    protected $fb;

    /**
     * Time failed
     * 
     * @var Integer
     */

    public function __construct(Post $post, Reply $reply)
    {
        $this->post = $post;
        $this->reply = $reply;
        $this->attempt = 0;
        $this->fb = app()->make('facebook');
    }

    /**
     * Publish reply to facebook
     * 
     * @return \App\Reply|false
     */
    public function publish() 
    {
        if ($this->attempt == 2) {
            $this->reply->status = Reply::STATUS_PUBLISH_FAILED;
            $this->reply->save();

            return false;
        }
        
        $this->attempt += 1;
        $ret = $this->fb->post($this->getFbEndPoint(), [
            'message'   => $this->getMessage(),
        ], $this->post->page->access_token);
        
        if ($ret->isError())
        {
            $pageFbService = new PageFacebookService();
            $pageFbService->refreshToken($this->post->page);
            
            return $this->publish();
        }

        $this->updateFbInfo($ret->getDecodedBody());
         
        return $this->reply;
    }

    /**
     * Update post with Facebook id
     * 
     * @return void
     */
    public function updateFbInfo()
    {
        $this->reply->status = Reply::STATUS_PUBLISHED;
        $this->reply->published_at = Carbon::now()->toDateTimeString();
        $this->reply->save();
    }

    /**
     * Get message content to reply
     * 
     * @return string
     */
    public function getMessage() {
        $message = '';

        if ($this->reply->type && !empty($this->reply->message)) 
            $message = str_replace("{{link}}", $this->post->getFbPostLink(), $this->reply->message);
         
        return $message;
    }

    /**
     * Get Facebook object related reply endpoint
     * 
     * @return string
     */
    public function getFbEndPoint() 
    {   
        $endPoint = '';
        switch ($this->reply->type) {
            case Reply::TYPE_VIDEO:
                $endPoint .= $this->reply->fb_target_id . '/comments';            
                 break;   
            case Reply::TYPE_VISITOR_POST:
                $endPoint .= $this->reply->fb_target_id . '/comments';
                break;
            
            case Reply::TYPE_PHOTO_COMMENT;
                $endPoint .= $this->reply->fb_target_id . '/comments';
                break;
            
            case Reply::TYPE_MESSAGE;
                $endPoint .= $this->getConversionId()  . '/messages';
                break;
            
            default :
                break;
        }
        
        return $endPoint;
    }

    /**
     * Get conversation id
     * 
     * @return string
     */
    public function getConversionId() {
        $col = (preg_match('/^[0-9]*$/', $this->reply->fb_target_id)) ? 'fb_thread_key' : 'fb_sender_name';
        
        //Assume a thread had been indexed with system
        $conv = Conversation::where($col, $this->reply->fb_target_id)
            ->where('page_id',$this->reply->page_id)
            ->first();
        
        if ($conv) {              
            return $conv->fb_id;
        }
        return '';
    }

    public function __get($name)
    {
        if (property_exists($this, $name));
            return $this->$name;
        return null;
    }
}