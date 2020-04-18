<?php

namespace App\Service\Publish;

use App\Comment;
use App\Post;
use App\Reply;
use Illuminate\Support\Facades\Auth;

class PublishCreateService extends AbstractPublish
{
    /**
     * @inheritdoc
     */
    public function save()
    {   
        $data = $this->getPostInfo();
        $data['user_id'] = Auth::user()->id;
        $data['page_id'] = $this->page->id;
        
        $post = Post::create($data);
        $post = $post->fresh();
        $post->scheduled_at_tz = $post->getScheduledAtTimezone($this->page->timezone);

        $post->comment = $this->saveComment($post);
        $post->reply = $this->saveReply($post);

        return $post;
    }

    /**
     * Save and return post related comment
     * 
     * @return \App\Comment|null
     */
    protected function saveComment(Post $post)
    {
        if (empty($this->data['comment']))
            return null;
        
        $comment = Comment::create([
            'message'   => $this->data['comment'],
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id
        ]);

        return $comment;
    }

     /**
     * Save and return post related conversation reply
     * 
     * @return \App\Reply|null
     */
    protected function saveReply(Post $post)
    {
        if (empty($this->data['target_url']))
            return null;
        if (empty($this->data['reply_message']))
            return null;
        
        $reply = Reply::create([
            'message'   => $this->data['reply_message'],
            'post_id'   => $post->id,
            'user_id'   => Auth::id(),
            'page_id'   => $this->page->id,
            'type'      => $this->extractReplyType(),
            'fb_target_id' => $this->extractReplyTargetId()
        ]);

        return $reply;
    }
}
