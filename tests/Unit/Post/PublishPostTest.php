<?php

namespace Tests\Unit\Post;

use App\Page;
use App\Post;
use App\Service\Post\FbPostPublishService;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\Mock;

class PublishPostTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetPostPhotoUrlDataForFbPublish()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_photo_url');

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $resp = $fbPostPublishService->preparePostData();

        $this->assertEquals($resp, [
            'caption' => $post->message,
            'url'   => $post->media_url
        ]);
    }


    public function testGetPostPhotoFileDataForFbPublish()
    {
        list($user, $page) = $this->addUserPage();
        $post = factory(Post::class)->states('type_photo_url')->create([
            'user_id'   => $user->id,
            'page_id'   => $page->id,
            'media_url'   => "page/{$page->id}/photo/image.png"
        ]);

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $resp = $fbPostPublishService->preparePostData();
        
        $this->assertEquals([
            'caption' => $post->message,
            'url'   => env('APP_URL') . "/storage/page/{$page->id}/photo/image.png"
        ], $resp);
    }

    public function testGetPostLinkDataForFbPublish()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link');

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $resp = $fbPostPublishService->preparePostData();
        
        $this->assertEquals([
            'message' => $post->message,
            'link'   => $post->link
        ], $resp);
    }

    public function testGetPostVideoDataForFbPublish()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_video');
        
        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('videoToUpload')->andReturn('video');
        app()->instance('facebook', $fbMock);

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $resp = $fbPostPublishService->preparePostData();
        
        $this->assertArraySubset([
            'caption' => $post->message,
            'title'   => $post->video_title
        ], $resp);
    }

    public function testFbEndPointPhotoAlbumPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_photo_url', [
            'fb_album_id' => 123456
        ]);

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $path = $fbPostPublishService->getFbEndPoint();

        $this->assertEquals('/123456/photos', $path);
    }

    public function testFbEndPointPhotoNoDefAlbumPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_photo_url');

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $path = $fbPostPublishService->getFbEndPoint();

        $this->assertEquals("/{$page->fb_id}/photos", $path);
    }

    public function testFbEndPointPhotoDefAlbumPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_photo_url');
        $page->def_fb_album_id = '1234567';
        $page->save();

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $path = $fbPostPublishService->getFbEndPoint();

        $this->assertEquals("/{$page->def_fb_album_id}/photos", $path);
    }

    public function testFbEndPointLinkPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link');

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $path = $fbPostPublishService->getFbEndPoint();

        $this->assertEquals("/{$page->fb_id}/feed", $path);
    }

    public function testFbEndPointVideoPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_video');

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $path = $fbPostPublishService->getFbEndPoint();

        $this->assertEquals("/{$page->fb_id}/videos", $path);
    }

    public function testPublishSuccess()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link');

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(false);
        $ret = [
            'id' => 12345,
            'post_id' => 123457
        ];
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn($ret);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $savedPost = $fbPostPublishService->publish();

        $this->assertEquals($savedPost->status, Post::STATUS_PUBLISHED);
        $this->assertEquals($savedPost->fb_id, $ret['id']);
        $this->assertEquals($savedPost->fb_post_id, $ret['post_id']);
    }

    public function testPublishFailed()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link');

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(true);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            'access_token' => 'access_token_string'
        ]);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $fbPostPublishService = new FbPostPublishService($post, $page);
        $savedPost = $fbPostPublishService->publish();

        $this->assertEquals($fbPostPublishService->attempt, 2);
        $this->assertEquals($savedPost, false);
    }

    public function addUserPagePost($type, $data=[])
    {
        list($user, $page) = $this->addUserPage();

        $data = array_merge($data, [
            'user_id'   => $user->id,
            'page_id'   => $page->id
        ]);

        $post = factory(Post::class)->states($type)->create($data);

        return [$user, $page, $post];
    }

    public function addUserPage()
    {
        $user = factory(User::class)->create();
        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        return [$user, $page];
    }
}
