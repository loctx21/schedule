<?php

namespace App\Service\Publish;

use App\Comment;
use App\Page;
use App\Post;
use App\Reply;
use Illuminate\Support\Facades\Auth;

class PublishEditService extends AbstractPublish
{

    /**
     * @var \App\Post
     */
    protected $post;
    
    public function __construct($data, Page $page, Post $post)
    {
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

        if (empty($this->data['reply_message']))
            return $reply;
        
        if (!$reply)
            $reply = new Reply;

        $reply->fill([
            'message'   => $this->data['reply_message'],
            'post_id'   => $this->post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id,
            'fb_target_id' => $this->data['fb_target_id']
        ]);
        $reply->save();

        return $reply;
    }
}
