<?php

namespace Tests\Feature\Publish;

use App\Page;
use App\Post;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

class UpdatePublishTest extends TestCase
{
    public function testUnAuthorizedUser()
    {
        $response = $this->post("/api/post/100");
        
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testEditPostNotBelongPage()
    {
        $user = factory(User::class)->create();
        $user1 = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $page = factory(Page::class)->create();
        $post = factory(Post::class)->states('type_photo_url')->create([
            'user_id' => $user1->id,
            'page_id' => $page->id
        ]);

        $response = $this->json('POST', "/api/post/{$post->id}", [
            'comment'  => 'test'
        ]);
        
        $response->assertStatus(403);
    }
}
