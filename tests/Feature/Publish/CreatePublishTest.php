<?php

namespace Tests\Feature\Publish;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Passport\Passport;

class CreatePublishTest extends TestCase
{
    public function testUnAuthorizedUser()
    {
        $response = $this->post('/api/post');
        
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testMissingInput()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/post', [
            'comment'  => 'test'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message', 'post_type', 'post_mode']);
    }

    public function testInvaledMainInput()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/post', [
            'message'  => 'test',
            'asset_mode'  => 'media',
            'post_type' => 'abc',
            'post_mode' => 'later',
            'fb_album_id' => 'character'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'asset_mode', 'post_type', 'post_mode', 'fb_album_id'
        ]);
    }

    public function testInvaledVideoInput()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/post', [
            'message'  => 'test',
            'asset_mode'  => 'url',
            'url' => 'https://laravel.com/docs/5.8/validation#rule-string',
            'post_type' => 'video',
            'post_mode' => 'now',
            'url' => 'http://google.com/a.png'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'video_title', 'url'
        ]);
    }

    public function testInvaledTimePostInput()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/post', [
            'message'  => 'test',
            'asset_mode'  => 'url',
            'url' => 'https://laravel.com/docs/5.8/validation#rule-string',
            'post_type' => 'video',
            'video_title' => 'title',
            'post_mode' => 'schedule',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'date', 'time_hour', 'time_minute'
        ]);
    }

    public function testInvaledLinkPostInput()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $response = $this->json('POST', '/api/post', [
            'message'  => 'test',
            'post_type' => 'link',
            'post_mode' => 'now',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'link'
        ]);
    }

    public function testInvaledPhotoPostInput()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        $file = UploadedFile::fake()->image('avatar.csv');

        $response = $this->json('POST', '/api/post', [
            'message'  => 'test',
            'post_type' => 'photo',
            'post_mode' => 'now',
            'asset_mode' => 'file',
            'post_file' => $file
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'post_file'
        ]);
    }

    public function testSavePublishPhotoSuccessfully()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);

        
    }
}
