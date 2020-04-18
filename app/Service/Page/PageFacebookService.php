<?php

namespace App\Service\Page;

use App\Page;

class PageFacebookService {

    /**
     * Refresh Facebook page access token
     * 
     * @param \App\Page $page
     * 
     * @return \App\Page
     */
    public function refreshToken(Page $page)
    {
        $user = $page->users()->orderBy('created_at','desc')->first();
        $fb = app()->make('facebook');

        $fbRes = $fb->get('/' . $page->fb_id . '?fields=access_token', $user->fb_access_token);
        $page->access_token = $fbRes->getDecodedBody()['access_token'];

        $page->save();

        return $page;
    }
}