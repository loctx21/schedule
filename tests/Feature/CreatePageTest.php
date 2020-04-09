<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;

class CreatePageTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUnAuthorizedUser()
    {
        $response = $this->post('/api/page');
        
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testMissingInformation()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/page', [
            'name'  => 'test'
        ]);
        
        $response->assertStatus(422);
    }

    public function testNoneNumericFbId()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/page', [
            'name'  => 'test',
            'access_token' => 'adasdadad',
            'fb_id' => 'asdd'
        ]);
        
        $response->assertStatus(422);
        $response->assertSeeText('fb_id');
    }

    public function testNoneNumericDefFbAlbumId()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/page', [
            'name'  => 'test',
            'access_token' => 'adasdadad',
            'fb_id' => 123,
            'def_fb_album_id' => '123as'
        ]);
        
        $response->assertStatus(422);
        $response->assertSeeText('def_fb_album_id');
    }

    public function testAddFanpageSuccess()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/page', [
            'name'  => 'test',
            'access_token' => 'adasdadad',
            'fb_id' => 123
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('pages', [
            'fb_id' => 123,
            'name'  => 'test'
        ]);
        $response->assertJsonFragment(['fb_id' => 123, 'name' => 'test']);
    }
}
