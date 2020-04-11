<?php

namespace Tests\Feature\Publish;

use App\Comment;
use App\Page;
use App\Post;
use App\Reply;
use App\Service\Publish\PublishCreateService;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Mockery;

class CreatePublishServiceTest extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetPublishNowPhotoPostFileInfo()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.png');
        $page = factory(Page::class)->create();

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertEquals($photoData, [
            'message'  => 'test photo message',
            'type'  => Post::TYPE_PHOTO_ID,
            'media_url'  => "page/{$page->id}/photo/image.png"
        ]);
    }

    public function testGetPublishLaterPhotoPostUrlInfo()
    {
        $page = factory(Page::class)->create();
        
        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'url',
            'url' => 'https://google.com/a.png',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertEquals($photoData, [
            'message'  => 'test photo message',
            'type'  => Post::TYPE_PHOTO_ID,
            'media_url'  => 'https://google.com/a.png'
        ]);
    }

    public function testGetPostNowInfo()
    {
        $page = factory(Page::class)->create();
        $data = [ 
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostScheduleInfo();

        $this->assertEquals($photoData, []);
    }

    public function testGetPublishNowVideoPostFileInfo()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('video.mp4');
        $page = factory(Page::class)->create();

        $data = [ 
            'message'  => 'test video message',
            'post_type' => 'video',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertEquals($photoData, [
            'message'  => 'test video message',
            'type'  => Post::TYPE_VIDEO_ID,
            'media_url'  => "page/{$page->id}/video/video.mp4"
        ]);
    }

    public function testGetPublishNowVideoPostUrlInfo()
    {
        $page = factory(Page::class)->create();

        $data = [ 
            'message'  => 'test video message',
            'post_type' => 'video',
            'asset_mode' => 'url',
            'url' => 'https://google.com/a.mp4',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertEquals($photoData, [
            'message'  => 'test video message',
            'type'  => Post::TYPE_VIDEO_ID,
            'media_url'  => "https://google.com/a.mp4"
        ]);
    }

    public function testGetPostScheduleLaterInfo()
    {
        $page = factory(Page::class)->create();
        $data = [ 
            'post_mode' => 'schedule',
            'date' => '2020-04-13',
            'time_hour' => 10,
            'time_minute' => "00"
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostScheduleInfo();

        $this->assertEquals($photoData, [
            'scheduled_at' => '2020-04-13 15:00:00'
        ]);
    }

    public function testGetPhotoUpload()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');
        $page = factory(Page::class)->create();

        $data = [ 
            'post_type' => 'photo',
            'post_file' => $file
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoName = $publishCreateService->getFileUpload();

        $final_path = "page/{$page->id}/photo/image.jpg";
        $this->assertEquals($final_path, $photoName);
        Storage::disk('local')->assertExists($final_path);
    }

    public function testGetPhotoRemote()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.jpg');
        $page = factory(Page::class)->create();

        $data = [ 
            'post_type' => 'photo',
            'url' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
            'save_file' => 1
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoName = $publishCreateService->getRemoteFile();

        $this->assertEquals("page/{$page->id}/photo/googlelogocolor272x92dp.png", $photoName);
        Storage::disk('local')->assertExists("page/{$page->id}/photo/googlelogocolor272x92dp.png");
    }

    public function testCreatePublishNowLinkInfo()
    {
        $page = factory(Page::class)->create();
        
        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'link',
            'link' => 'https://google.com/a.png',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertEquals($photoData, [
            'message'  => 'test photo message',
            'type'  => Post::TYPE_LINK_ID,
            'link'  => 'https://google.com/a.png'
        ]);
    }

    public function testCreatePublishNowPhotoPostFileNoCommenReplyInfo()
    {
        Storage::fake('local');
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('image.png');
        $page = factory(Page::class)->create();

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $post = $publishCreateService->save();

        $this->assertEquals($post->message, 'test photo message');
        $this->assertEquals($post->type, Post::TYPE_PHOTO_ID);
        $this->assertEquals($post->media_url, "page/{$page->id}/photo/image.png");
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);
        
        $this->assertEquals($post->comment, null);
        $this->assertEquals($post->reply, null);
    }

    public function testCreatePublishNowPhotoPostFileWithCommenReplyInfo()
    {
        Storage::fake('local');
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $file = UploadedFile::fake()->image('image.png');
        $page = factory(Page::class)->create();

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now',
            'comment' => 'This is the comment',
            'reply_message' => 'This is the reply message',
            'fb_target_id' => 'Loc Nguyen'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $post = $publishCreateService->save();

        $this->assertEquals($post->message, 'test photo message');
        $this->assertEquals($post->type, Post::TYPE_PHOTO_ID);
        $this->assertEquals($post->media_url, "page/{$page->id}/photo/image.png");
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);

        $this->assertEquals($post->comment->message, 'This is the comment');
        $this->assertEquals($post->comment->status, Comment::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->comment->user_id, $user->id);
        $this->assertEquals($post->comment->page_id, $page->id);
        $this->assertEquals($post->comment->post_id, $post->id);

        $this->assertEquals($post->reply->message, 'This is the reply message');
        $this->assertEquals($post->reply->status, Reply::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->reply->user_id, $user->id);
        $this->assertEquals($post->reply->page_id, $page->id);
        $this->assertEquals($post->reply->post_id, $post->id);
        $this->assertEquals($post->reply->fb_target_id, 'Loc Nguyen');
    }
}
