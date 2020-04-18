<?php

namespace App\Service\Page;

use App\Http\Requests\CreatePage;
use App\Page;
use App\User;
use Illuminate\Support\Facades\Auth;

class PageCreateService {


    /**
     * Create page from request data
     * 
     * @param array $data
     * 
     * @return \App\Page
     */
    public function create($data)
    {
        $page = new Page;
        
        $page->fill($data);
        $page->access_token = $this->getPageAccessToken($data['fb_id']);
        $page->save();

        $user = auth()->user();
        $user->pages()->attach($page->id);
        
        return $page;
    }

    /**
     * Get Page long lived access token from Facebook
     * 
     * @param Integer $fb_id
     * 
     * @return string
     */
    public function getPageAccessToken($fb_id)
    {
        $fb = app()->make('facebook');
        $user = auth()->user();
        
        $fbRes = $fb->get("/{$fb_id}?fields=access_token", $user->fb_access_token);
        return $fbRes->getDecodedBody()['access_token'];
    }
}