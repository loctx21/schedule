<?php

namespace App\Service\Page;

use App\Page;

class PageUpdateService {

    /**
     * Update page data
     * 
     * @param \App\Page $page
     * @param array $data
     * 
     * @return \App\Page
     */
    public function update(Page $page, $data)
    {
        $finalData = array_intersect_key($data, array_flip(['def_fb_album_id', 'conv_index',
            'schedule_time', 'message_reply_tmpl', 'post_reply_tmpl', 'timezone']));
        $page->update($finalData);
        
        return $page;
    }
}