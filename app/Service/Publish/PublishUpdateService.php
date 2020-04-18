<?php

namespace App\Service\Publish;

use App\Comment;
use App\Post;
use App\Reply;
use Illuminate\Support\Facades\Auth;

class PublishUpdateService extends AbstractPublish
{

    /**
     * @var \App\Post
     */
    protected $post;
    
    public function __construct($data, Post $post)
    {   
        $page = $post->page;
        parent::__construct($data, $page);
        $this->post = $post;
    }

    /**
     * @inheritdoc
     */
    public function save()
    {   
        $data = $this->getPostInfo();
        $data['user_id'] = Auth::user()->id;
        $data['page_id'] = $this->page->id;
        
        $this->post->fill($data);
        $this->post->save();

        $this->post->scheduled_at_tz = $this->post->getScheduledAtTimezone($this->page->timezone);
        $this->post->comment = $this->saveComment();
        $this->post->reply = $this->saveReply();

        return $this->post;
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
            'type'      => $this->extractReplyType(),
            'fb_target_id' => $this->extractReplyTargetId()
        ]);
        $reply->save();

        return $reply;
    }
}
