<?php

namespace App\Service\Post;

use App\Page;
use App\Post;
use App\Service\Page\PageFacebookService;
use Carbon\Carbon;

class FbPostPublishService {

    /**
     * The post to publish
     * 
     * @var \App\Post
     */
    protected $post;

    /**
     * Page to publish the post
     * 
     * @var \App\Page
     */
    protected $page;

    /**
     * Facecbook service object from SDK
     * 
     * @var Facebook\Facebook;
     */
    protected $fb;

    /**
     * Time failed
     * 
     * @var Integer
     */
    protected $attempt;

    public function __construct(Post $post, Page $page)
    {
        $this->post = $post;
        $this->page = $page;
        $this->attempt = 0;
        $this->fb = app()->make('facebook');
    }

    /**
     * Publish reply to facebook
     * 
     * @return \App\Post|false
     */
    public function publish()
    {
        //Mark post as publish failed after 2 attemps
        if ($this->attempt == 2) {
            $this->post->status = Post::STATUS_PUBLISH_FAILED;
            $this->post->save();

            return false;
        }

        $this->attempt += 1;
        $resp = $this->fbPublish();

        if ($resp->isError())
        {
            $pageFbService = new PageFacebookService();
            $pageFbService->refreshToken($this->page);
            
            return $this->publish();
        }

        $this->updatePostFbInfo($resp->getDecodedBody());

        return $this->post;
    }

    /**
     * Update post with Facebook id
     * 
     * @return void
     */
    public function updatePostFbInfo($decodeBody)
    {
        $this->post->fb_id   = $decodeBody['id'];
        if (array_key_exists('post_id', $decodeBody))
            $this->post->fb_post_id   = $decodeBody['post_id'];

        $this->post->status = Post::STATUS_PUBLISHED;
        $this->post->published_at = Carbon::now()->toDateTimeString();
        $this->post->save();
    }

    /**
     * Setup and call Facebook publish post api
     * 
     * @return FacebookResponse
     *
     * @throws FacebookSDKException
     */
    public function fbPublish()
    {
        $endPoint = $this->getFbEndPoint();
        $data = $this->preparePostData();

        return $this->fb->post($endPoint, $data, $this->page->acccess_token);
    }

    /**
     * Prepare post data for post api
     * 
     * @return array
     */
    public function preparePostData()
    {
        switch ($this->post->type) {
            case Post::TYPE_LINK_ID:
                $data = [
                    'message'   => $this->post->message,
                    'link'      => $this->post->link    
                ];
                break;
            
            case Post::TYPE_PHOTO_ALBUM_ID:
                $data = [
                    'caption'   => $this->post->message,
                    'url'      => $this->post->media_url    
                ];
                break;

            case Post::TYPE_PHOTO_ID:
                $data = [
                    'caption'   => $this->post->message,
                    'url'      => $this->post->media_url    
                ];
                break;

            case Post::TYPE_VIDEO_ID:
                $data = [
                    'caption'    => $this->post->message,
                    'title'      => $this->post->video_title,
                    'source'     => $this->fb->videoToUpload($this->post->media_url)    
                ];
                break;
            
            default:
                $data = [];
                break;
        }

        return $data;
    }

    /**
     * Get Fb endpoint path
     * 
     * @return string
     */
    public function getFbEndPoint() {
        if ($this->post->type == Post::TYPE_PHOTO_ID) {
            if (!empty($this->post->fb_album_id))
                return '/' . $this->post->fb_album_id . '/photos';

            //Fix stupid facebook auto create new album by default album id
            $object_id = empty($this->page->def_fb_album_id) ? $this->page->fb_id 
                : $this->page->def_fb_album_id;
            return '/' . $object_id . '/photos';
        }
        
        if ($this->post->type == Post::TYPE_LINK_ID) 
            return  '/' . $this->page->fb_id . '/feed';
        
        if ($this->post->type == Post::TYPE_VIDEO_ID)
            return '/' . $this->page->fb_id . '/videos';
    }

    public function __get($name)
    {
        if (property_exists($this, $name));
            return $this->$name;
        return null;
    }
}