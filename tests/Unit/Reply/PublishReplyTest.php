<?php

namespace Tests\Unit\Reply;

use App\Conversation;
use App\Page;
use App\Post;
use App\Reply;
use App\Service\Reply\FbReplyPublishService;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PublishReplyTest extends TestCase
{
    public function testFbEndPointVideo()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_video');

        $service = new FbReplyPublishService($post, $reply);
        $path = $service->getFbEndPoint();

        $this->assertEquals("{$reply->fb_target_id}/comments", $path);
    }

    public function testFbEndPointVisitorPost()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_visitor_post');

        $service = new FbReplyPublishService($post, $reply);
        $path = $service->getFbEndPoint();

        $this->assertEquals("{$reply->fb_target_id}/comments", $path);
    }

    public function testFbEndPointComment()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_photo_comment');

        $service = new FbReplyPublishService($post, $reply);
        $path = $service->getFbEndPoint();

        $this->assertEquals("{$reply->fb_target_id}/comments", $path);
    }

    public function testFbEndPointConversation()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_message', [
            'fb_target_id' => 'target name'
        ]);
        $conv = factory(Conversation::class)->create([
            'page_id' => $page->id,
            'fb_sender_name' => 'target name'
        ]);

        $service = new FbReplyPublishService($post, $reply);
        $path = $service->getFbEndPoint();

        $this->assertEquals("{$conv->fb_id}/messages", $path);
    }

    public function testGetMessage()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_video', [
            'message' => '{{link}} message'
        ]);
        $post->fb_post_id = '123';
        $post->save();

        $service = new FbReplyPublishService($post, $reply);
        $message = $service->getMessage();

        $this->assertEquals("{$post->getFbPostLink()} message", $message);
    }

    public function testPublishFailed()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_photo_comment');

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(true);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            'access_token' => 'access_token_string'
        ]);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $fbReplyPublishService = new FbReplyPublishService($post, $reply);
        $fbReplyPublishService->publish();

        $this->assertEquals($fbReplyPublishService->attempt, 2);
        $this->assertEquals($reply->status, Reply::STATUS_PUBLISH_FAILED);
    }

    public function testPublishSuccess()
    {
        list($user, $page, $post, $reply) = $this->addUserPageReply('type_photo_comment');

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(false);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([]);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $fbPostPublishService = new FbReplyPublishService($post, $reply);
        $savedPost = $fbPostPublishService->publish();

        $this->assertEquals($savedPost->status, Reply::STATUS_PUBLISHED);
    }

    public function addUserPageReply($type, $data = [])
    {
        list($user, $page) = $this->addUserPage();

        $post = factory(Post::class)->create([
            'user_id'   => $user->id,
            'page_id'   => $page->id
        ]);

        $data = array_merge([
            'user_id'   => $user->id,
            'page_id'   => $page->id,
            'post_id'   => $post->id
        ], $data);
        $reply = factory(Reply::class)->states($type)->create($data);

        return [$user, $page, $post, $reply];
    }

    public function addUserPage()
    {
        $user = factory(User::class)->create();
        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        return [$user, $page];
    }
}
