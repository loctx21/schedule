<?php

namespace App\Service\Page;

use App\Helper\Utils;
use App\Page;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PagePublishtDataService {
    public function getPagePublish(Page $page)
    {
        $paginator = $page->posts()
            ->orderBy('scheduled_at', 'DESC')
            ->orderBy('id', 'DESC')
            ->with(['comment', 'reply'])
            ->paginate(30);

        foreach ($paginator as $post)
        {
            if (empty($post->scheduled_at))
                continue;

            $post->scheduled_at_tz = $post->getScheduledAtTimezone($page->timezone);
        }

        return $paginator;
    }
}