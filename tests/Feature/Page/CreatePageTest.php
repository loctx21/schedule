<?php

namespace Tests\Feature\Page;

use App\Page;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery;

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

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            'access_token' => 'access_token_string'
        ]);

        $fbMock = Mockery::mock('facebook');
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $data =  [
            'name'  => 'test',
            'access_token' => 'adasdadad',
            'fb_id' => $this->getUniqueFbId()
        ];
        $response = $this->json('POST', '/api/page', $data);
       
        $response->assertStatus(200);
        $this->assertDatabaseHas('pages', [
            'fb_id' => $data['fb_id'],
            'name'  => $data['name']
        ]);
        $this->assertEquals($user->pages()->count(), 1);
        $response->assertJsonFragment(['fb_id' => $data['fb_id'], 'name' => $data['name']]);
    }

    public function getUniqueFbId()
    {
        $page = Page::orderBy('id', 'DESC')->first();
        return $page ? $page->fb_id + 1 : 123;
    }
}
