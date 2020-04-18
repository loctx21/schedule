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

class GetPagePublishTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testGetPagePublish()
    {
        list($user, $page) = $this->addPageUser();
        for ($i = 0; $i < 10; $i++) 
        {
            $post = factory(Post::class)->state('type_photo_url')->create([
                'user_id'  => $user->id,
                'page_id'  => $page->id
            ]);

            $comment = factory(Comment::class)->create([
                'user_id'  => $user->id,
                'post_id'  => $post->id
            ]);

            $reply = factory(Reply::class)->state('type_visitor_post')->create([
                'user_id'  => $user->id,
                'page_id'  => $page->id,
                'post_id'  => $post->id
            ]);
        }
        
        $response = $this->json("GET", "/api/page/{$page->id}/post");

        $response->assertStatus(200);

        $json = json_decode($response->getContent());
        $this->assertNotNull($json->data[0]->comment);
        $this->assertNotNull($json->data[0]->reply);
    }


    public function addPageUser()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);
        $page = factory(Page::class)->create();

        return [$user, $page];
    }
}
