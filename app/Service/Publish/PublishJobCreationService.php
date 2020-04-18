<?php

namespace App\Service\Publish;

use App\Jobs\SchedulePost;
use App\Post;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PublishJobCreationService {

    public function schedule()
    {
        //Scheduler -> Multiple Sub schedulers -> multiple worker publisher 
        //to handle huge request

        $postIds = Post::where('status', Post::STATUS_NOT_PUBLISH)
            ->where('scheduled_at', '<=', Carbon::now())
            ->orderBy('scheduled_at', 'ASC')
            ->pluck('id')->toArray();

        if (!count($postIds))
            return;

        $start_post_id = $postIds[0];
        $process_id = null;

        try {
            foreach ($postIds as $post_id) {
                SchedulePost::dispatch($post_id);
                $process_id = $post_id;
            }

        } catch (\Exception $e) {

        }

        DB::table('posts')->where('status', Post::STATUS_NOT_PUBLISH)
            ->where('id', '>=', $start_post_id)
            ->where('id', '<=', $process_id)
            ->update(['status' => Post::STATUS_PROCESSING]);
    }
}