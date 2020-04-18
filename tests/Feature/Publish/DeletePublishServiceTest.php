<?php

namespace Tests\Feature\Publish;

use App\Comment;
use App\Page;
use App\Post;
use App\Reply;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

class DeletePublishServiceTest extends TestCase
{

    public function testDeletePublishedPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link',[
            'status' => Post::STATUS_PUBLISHED
        ]);
        Passport::actingAs($user);

        $response = $this->json("DELETE", "/api/post/{$post->id}");
        
        $response->assertStatus(403);
    }

    public function testDeleteProcessingPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link',[
            'status' => Post::STATUS_PROCESSING
        ]);
        Passport::actingAs($user);

        $response = $this->delete("/api/post/{$post->id}");
        
        $response->assertStatus(403);
    }

    public function testDeletePublishFailedPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link',[
            'status' => Post::STATUS_PUBLISH_FAILED
        ]);
        Passport::actingAs($user);

        $response = $this->delete("/api/post/{$post->id}");
        
        $response->assertStatus(403);
    }

    public function testDeleteValidPost()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link',[
            'status' => Post::STATUS_NOT_PUBLISH
        ]);
        Passport::actingAs($user);

        $comment = Comment::create([
            'message' => 'random message',
            'user_id' => $user->id,  
            'page_id' => $page->id,
            'post_id' => $post->id
        ]);
        $reply = Reply::create([
            'type' => Reply::TYPE_VISITOR_POST,
            'message' => 'random message',
            'user_id' => $user->id,  
            'page_id' => $page->id,
            'post_id' => $post->id,
            'target_url' => 'Loc Nguyen 1'
        ]);

        $response = $this->delete("/api/post/{$post->id}");

        $this->assertNull(Comment::find($comment->id));
        $this->assertNull(Reply::find($reply->id));
        $response->assertStatus(200);
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
