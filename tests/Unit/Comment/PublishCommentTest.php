<?php

namespace Tests\Unit\Comment;

use App\Comment;
use App\Page;
use App\Post;
use App\Service\Comment\FbCommentPublishService;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;

class PublishCommentTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testMatchFbEndPointComment()
    {
        list($user, $page, $post, $comment) = $this->addUserPagePostComment();

        $fbPostPublishService = new FbCommentPublishService($post, $comment);
        $path = $fbPostPublishService->getFbEndPoint();

        $this->assertEquals("/{$post->getFbObjectId()}/comments", $path);
    }

    public function testMisMatchFbEndPointComment()
    {
        list($user, $page, $post, $comment) = $this->addUserPagePostComment();
        $otherPost = factory(Post::class)->create([
            'user_id'   => $user->id,
            'page_id'   => $page->id
        ]); 
        
        $this->expectException(\Exception::class);

        $fbPostPublishService = new FbCommentPublishService($otherPost, $comment);
        $path = $fbPostPublishService->getFbEndPoint();
    }

    public function testPublishFailed()
    {
        list($user, $page, $post, $comment) = $this->addUserPagePostComment();

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(true);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            'access_token' => 'access_token_string'
        ]);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $fbCommentPublishService = new FbCommentPublishService($post, $comment);
        $fbCommentPublishService->publish();

        $this->assertEquals($fbCommentPublishService->attempt, 2);
        $this->assertEquals($comment->status, Comment::STATUS_PUBLISH_FAILED);
    }


    public function addUserPagePostComment()
    {
        list($user, $page) = $this->addUserPage();

        $post = factory(Post::class)->create([
            'user_id'   => $user->id,
            'page_id'   => $page->id
        ]);

        $comment = factory(Comment::class)->create([
            'user_id'   => $user->id,
            'post_id'   => $post->id
        ]);

        return [$user, $page, $post, $comment];
    }

    public function addUserPage()
    {
        $user = factory(User::class)->create();
        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        return [$user, $page];
    }

}
