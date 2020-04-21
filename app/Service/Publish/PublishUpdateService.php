<?php

namespace App\Service\Publish;

use App\Comment;
use App\Helper\Utils;
use App\Post;
use App\Reply;
use Illuminate\Support\Facades\Auth;

class PublishUpdateService extends AbstractPublish
{

    /**
     * @var \App\Post
     */
    protected $post;
    
    /**
     * Create a new page's publish updation service
     * 
     * @param array $data
     * @param \App\Post $post
     * @return void
     */
    public function __construct($data, Post $post)
    {   
        $this->page = $post->page;
        $this->post = $post;
        parent::__construct($data);
    }

    /**
     * Save and return post related comment
     * 
     * @return \App\Comment|null
     */
    protected function saveComment()
    {   
        $comment = $this->post->comments()->first();

        if (empty($this->data['comment']))
            return $comment;

        if (!$comment)
            $comment = new Comment;

        $comment->fill([
            'message'   => $this->data['comment'],
            'post_id'   => $this->post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id
        ]);
        $comment->save();

        return $comment;
    }

     /**
     * Save and return post related conversation reply
     * 
     * @return \App\Reply|null
     */
    protected function saveReply()
    {
        $reply = $this->post->replies()->first();
        
        if (!$reply) {
            if (empty($this->data['reply_message']))
                return null;

            if (empty($this->data['target_url']))
                return null;

            $reply = new Reply;
        }
            
        $reply->fill([
            'message'   => array_key_exists('reply_message', $this->data) ? $this->data['reply_message'] : "",
            'post_id'   => $this->post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id,
            'type'      => Reply::extractReplyType($this->post->target_url),
            'fb_target_id' => Reply::extractReplyTargetId($this->post->target_url)
        ]);
        $reply->save();

        return $reply;
    }
}
