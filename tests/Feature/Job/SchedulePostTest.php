<?php

namespace Tests\Feature\Job;

use App\Comment;
use App\Jobs\SchedulePost;
use App\Page;
use App\Post;
use App\Reply;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery;

class SchedulePostTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testHandleNoneProcessingPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link');
        
        foreach ([Post::STATUS_NOT_PUBLISH, Post::STATUS_PUBLISH_FAILED, Post::STATUS_PUBLISHED] as $value) {
            $post->status = $value;
            $post->save();

            $job = new SchedulePost($post->id);
            $job->handle();
            $post = $post->fresh();
            $this->assertEquals($value, $post->status);
        }
    }

    public function testPublishPostFailed()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link', [
            'status' => Post::STATUS_PROCESSING
        ]);
        $comment = factory(Comment::class)->create([
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(true);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            'access_token' => 'access_token_string'
        ]);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $job = new SchedulePost($post->id);
        $job->handle();

        $post = $post->fresh();
        $comment = $comment->fresh();

        $this->assertEquals(Post::STATUS_PUBLISH_FAILED, $post->status);
        $this->assertEquals(Comment::STATUS_NOT_PUBLISH, $comment->status);
    }

    public function testPublishPostSuccess()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link', [
            'status' => Post::STATUS_PROCESSING
        ]);
        $comment = factory(Comment::class)->create([
            'user_id' => $user->id,
            'post_id' => $post->id
        ]);

        $reply = factory(Reply::class)->states('type_visitor_post')->create([
            'user_id' => $user->id,
            'post_id' => $post->id,
            'page_id' => $page->id
        ]);

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(false);
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn(
            ['id' => 123], [], []
        );

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $job = new SchedulePost($post->id);
        $job->handle();

        $post = $post->fresh();
        $comment = $comment->fresh();
        $reply = $reply->fresh();

        $this->assertEquals(Post::STATUS_PUBLISHED, $post->status);
        $this->assertEquals(Comment::STATUS_PUBLISHED, $comment->status);
        $this->assertEquals(Reply::STATUS_PUBLISHED, $reply->status);
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
        Passport::actingAs($user, ['*']);
        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        return [$user, $page];
    }
}
