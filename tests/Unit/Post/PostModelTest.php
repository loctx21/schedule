<?php

namespace Tests\Unit\Post;

use App\Page;
use App\Post;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Laravel\Passport\Passport;

class PostModelTest extends TestCase
{
    public function testGetFbPostLinkAttribute()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link', [
            'fb_id' => 123
        ]);

        $this->assertEquals(null, $post->getFbPostLinkAttribute());

        $post->status = Post::STATUS_PUBLISHED;
        $post->save();
        $this->assertEquals("http://facebook.com/123", $post->getFbPostLinkAttribute());

        $post->fb_post_id = 1234;
        $post->save();
        $this->assertEquals("http://facebook.com/1234", $post->getFbPostLinkAttribute());
    }

    public function testGetMediaUrlAttribute()
    {
        list($user, $page, $post) = $this->addUserPagePost('type_link', [
            'media_url' => "http://facebook.com/image.png"
        ]);

        $this->assertEquals("http://facebook.com/image.png", $post->getMediaUrlAttribute());

        $app_url = env('APP_URL');
        $post->media_url = "page/post/image.png";
        $post->save();
        $this->assertEquals("/storage/page/post/image.png", $post->getMediaUrlAttribute());
    }

    public function testGetStatusTextAttribute()
    {
        $post = factory(Post::class)->make();

        $post->status = "";
        $this->assertEquals("", $post->getStatusTextAttribute());

        $post->status = Post::STATUS_PUBLISHED;
        $this->assertEquals(Post::STATUS_TEXT[Post::STATUS_PUBLISHED], $post->getStatusTextAttribute());

        $post->status = 100;
        $this->assertEquals("", $post->getStatusTextAttribute());
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
