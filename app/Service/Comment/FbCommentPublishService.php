<?php

namespace App\Service\Comment;

use App\Comment;
use App\Post;
use App\Service\Page\PageFacebookService;
use Carbon\Carbon;

class FbCommentPublishService {

    /**
     * The post to publish
     * 
     * @var \App\Post
     */
    protected $post;

    /**
     * The comment to publish
     * 
     * @var \App\Comment
     */
    protected $comment;
    
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
    protected $attempt;

    public function __construct(Post $post, Comment $comment)
    {
        $this->post = $post;
        $this->comment = $comment;
        $this->attempt = 0;
        $this->fb = app()->make('facebook');
    }

    /**
     * Publish reply to facebook
     * 
     * @return \App\Comment|false
     */
    public function publish() 
    {
        if ($this->attempt == 2) {
            $this->comment->status = Comment::STATUS_PUBLISH_FAILED;
            $this->comment->save();

            return false;
        }
        
        $this->attempt += 1;
        $ret = $this->fb->post($this->getFbEndPoint(), [
            'message'   => $this->comment->message,
        ], $this->post->page->access_token);
        
        if ($ret->isError())
        {
            $pageFbService = new PageFacebookService();
            $pageFbService->refreshToken($this->post->page);
            
            return $this->publish();
        }

        $this->updateFbInfo($ret->getDecodedBody());
         
        return $this->comment;
    }

    /**
     * Update comment with Facebook id
     * 
     * @return void
     */
    public function updateFbInfo($decodeBody)
    {
        $this->fb_post_id   = $decodeBody['post_id'];
        $this->status       = Comment::STATUS_PUBLISHED;
        $this->published_at = Carbon::now()->toDateTimeString();
        
        $this->comment->save();
    }

    /**
     * Get Facebook endpoint to publish comment
     * 
     * @return string
     * 
     * @throws \Exception
     */
    public function getFbEndPoint() {
        if ($this->comment->post_id != $this->post->id)
            throw new \Exception('Post comment mismatch');
        return '/' . $this->post->getFbObjectId() . '/comments';
    }

    public function __get($name)
    {
        if (property_exists($this, $name));
            return $this->$name;
        return null;
    }
}