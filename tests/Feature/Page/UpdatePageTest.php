<?php

namespace Tests\Feature\Page;

use App\Page;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

class UpdatePageTest extends TestCase
{
    public function testUnAuthorizedUser()
    {
        $response = $this->put('/api/page/1');

        $response->assertStatus(302);       
        $response->assertRedirect('login');
    }

    public function testEditOtherUserPage()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $page = factory(Page::class)->create();
        $user1 = factory(User::class)->create();
        $page->users()->attach($user1->id);

        $response = $this->put("/api/page/{$page->id}");
        
        $response->assertStatus(403);
    }

    public function testEditInvalidPageInfo()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        $response = $this->json("PUT", "/api/page/{$page->id}", [
            'def_fb_album_id' => 'asd',
            'conv_index' => 'a',
            'schedule_time' => 123,
            'timezone' => 7
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['def_fb_album_id', 'conv_index', 
            'schedule_time', 'timezone']);
    }

    public function testEditValidPageInfo()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        $data = [
            'def_fb_album_id' => 1235234,
            'conv_index' => 1,
            'schedule_time' => '7:00,113:30',
            'message_reply_tmpl' => 'Test message reply template',
            'post_reply_tmpl' => 'Test post reply template',
            'timezone'  => 'Asia/Yakutsk'
        ];

        $response = $this->json("PUT", "/api/page/{$page->id}", $data);
        
        $page = Page::find($page->id);
        $response->assertStatus(200);
        
        $this->assertArraySubset($data, $page->toArray());
    }
}
