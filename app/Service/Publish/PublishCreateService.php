<?php

namespace App\Service\Publish;

use App\Comment;
use App\Helper\Utils;
use App\Page;
use App\Post;
use App\Reply;
use Illuminate\Support\Facades\Auth;

class PublishCreateService extends AbstractPublish
{
    /**
     * Create a new page's publish updation service
     * 
     * @param array $data
     * @param \App\Page $page
     * @return void
     */
    public function __construct($data, Page $page)
    {   
        $this->page = $page;
        $this->post = new Post;
        parent::__construct($data);
    }

    /**
     * @inheritdoc
     */
    protected function saveComment()
    {
        if (empty($this->data['comment']))
            return null;
        
        $comment = Comment::create([
            'message'   => $this->data['comment'],
            'post_id'   => $this->post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id
        ]);

        return $comment;
    }

     /**
     * @inheritdoc
     */
    protected function saveReply()
    {
        if (empty($this->data['target_url']))
            return null;
        if (empty($this->data['reply_message']))
            return null;
        
        $reply = Reply::create([
            'message'   => $this->data['reply_message'],
            'post_id'   => $this->post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id,
            'type'      => Reply::extractReplyType($this->data['target_url']),
            'fb_target_id' => Reply::extractReplyTargetId($this->data['target_url'])
        ]);

        return $reply;
    }
}
