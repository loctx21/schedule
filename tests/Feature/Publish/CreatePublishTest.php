<?php

namespace Tests\Feature\Publish;

use App\Page;
use App\Post;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Passport\Passport;
use Mockery;

class CreatePublishTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testUnAuthorizedUser()
    {
        $response = $this->post("/api/page/1/post");
        
        $response->assertStatus(302);
        $response->assertRedirect('login');
    }

    public function testMissingInput()
    {
        list($user, $page) = $this->addPageUser();

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
            'comment'  => 'test'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['message', 'post_type', 'post_mode']);
    }

    public function testInvalidMainInput()
    {
        list($user, $page) = $this->addPageUser();

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
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

    public function testInvalidVideoInput()
    {
        list($user, $page) = $this->addPageUser();

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
            'message'  => 'test',
            'asset_mode'  => 'url',
            'media_url' => 'https://laravel.com/docs/5.8/validation#rule-string',
            'post_type' => 'video',
            'post_mode' => 'now'
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'video_title', 'media_url'
        ]);
    }

    public function testInvalidTimePostInput()
    {
        list($user, $page) = $this->addPageUser();

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
            'message'  => 'test',
            'asset_mode'  => 'url',
            'media_url' => 'https://laravel.com/docs/5.8/validation#rule-string',
            'post_type' => 'video',
            'video_title' => 'title',
            'post_mode' => 'schedule',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'date', 'time_hour', 'time_minute'
        ]);
    }

    public function testInvalidLinkPostInput()
    {
        list($user, $page) = $this->addPageUser();

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
            'message'  => 'test',
            'post_type' => 'link',
            'post_mode' => 'now',
        ]);
        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'link'
        ]);
    }

    public function testInvalidPhotoPostInput()
    {
        list($user, $page) = $this->addPageUser();

        $file = UploadedFile::fake()->image('avatar.csv');

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
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

    public function testSavePublishLinkScheduledSuccess()
    {
        list($user, $page) = $this->addPageUser();
        
        $response = $this->json('POST', "/api/page/{$page->id}/post", [
            "post_type" => "link",
            "asset_mode" => "url",
            "message" => "This is the link post",
            "media_url" =>"",
            "save_file" => false,
            "post_mode" => "schedule",
            "comment" => "test post reply  {{link}}",
            "reply_message" => "test message {{link}}",
            "target_url" => "",
            "video_title" => "",
            "link" => "https://schedule.themesjuice.com/posts/1",
            "date" => "2020-04-24",
            "time_hour" => 7,
            "time_minute" => 0
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "message" => "This is the link post",
            "scheduled_at" => "2020-04-24 12:00:00"
        ]);
    }

    public function testSavePublishLinkNowSuccess()
    {
        list($user, $page) = $this->addPageUser();
        
        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('isError')->andReturn(false);
        $ret = [
            'id' => 12345,
            'post_id' => 123457
        ];
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn($ret);

        $fbMock = Mockery::mock();
        $fbMock->shouldReceive('post')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $response = $this->json('POST', "/api/page/{$page->id}/post", [
            "post_type" => "link",
            "asset_mode" => "url",
            "message" => "This is the link post",
            "media_url" =>"",
            "save_file" => false,
            "post_mode" => "now",
            "comment" => "test post reply  {{link}}",
            "reply_message" => "test message {{link}}",
            "target_url" => "",
            "video_title" => "",
            "link" => "https://schedule.themesjuice.com/posts/1"
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            "message" => "This is the link post",
            "status" => Post::STATUS_PUBLISHED
        ]);
    }

    public function addPageUser()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user, ['*']);
        $page = factory(Page::class)->create();

        return [$user, $page];
    }
}
