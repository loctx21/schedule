<?php

namespace App\Jobs;

use App\Post;
use App\Service\Comment\FbCommentPublishService;
use App\Service\Post\FbPostPublishService;
use App\Service\Reply\FbReplyPublishService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class SchedulePost implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Post id
     * 
     * @var Integer
     */
    protected $post_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($post_id)
    {
        $this->post_id = $post_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $post = Post::find($this->post_id);
        if (!$post || $post->status != Post::STATUS_PROCESSING)
            return;

        $fbPostPublishService = new FbPostPublishService($post, $post->page);
        if (!$fbPostPublishService->publish())
            return;
        
        $comment = $post->comments()->first();
        if ($comment) {
            $fbCommentPublishService = new FbCommentPublishService($post, $comment);
            $fbCommentPublishService->publish();
        }

        $reply = $post->replies()->first();
        if ($reply) {
            $fbReplyPublishService = new FbReplyPublishService($post, $reply);
            $fbReplyPublishService->publish();
        }
    }
}
