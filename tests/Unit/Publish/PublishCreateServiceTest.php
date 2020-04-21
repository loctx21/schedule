<?php

namespace Tests\Unit\Publish;

use App\Comment;
use App\Jobs\SchedulePost;
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
use Queue;

class PublishCreateServiceTest extends TestCase
{

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testGetPublishNowPhotoPostFileInfo()
    {
        Storage::fake('local');
        list($user, $page) = $this->addUserPage();
        $file = UploadedFile::fake()->image('image.png');

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertArraySubset([
            'target_url' => '',
            'message'  => 'test photo message',
            'type'  => Post::TYPE_PHOTO_ID,
            'media_url'  => "page/{$page->id}/photo/image.png"
        ], $photoData);
        $this->assertNotNull($photoData['scheduled_at']);
    }

    public function testGetPublishLaterPhotoPostUrlInfo()
    {
        list($user, $page) = $this->addUserPage();
        
        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'url',
            'media_url' => 'https://google.com/a.png',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertArraySubset([
            'target_url' => '',
            'message'  => 'test photo message',
            'type'  => Post::TYPE_PHOTO_ID,
            'media_url'  => 'https://google.com/a.png'
        ], $photoData);
        $this->assertNotNull($photoData['scheduled_at']);
    }

    public function testGetPostNowInfo()
    {
        list($user, $page) = $this->addUserPage();
        $data = [ 
            'post_mode' => 'now',
            'post_type' => 'link'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostScheduleInfo();

        $this->assertNotNull($photoData['scheduled_at']);
    }

    public function testGetPublishNowVideoPostFileInfo()
    {
        Storage::fake('local');
        list($user, $page) = $this->addUserPage();
        $file = UploadedFile::fake()->image('video.mp4');

        $data = [ 
            'message'  => 'test video message',
            'post_type' => 'video',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertArraySubset([
            'target_url' => '',
            'message'  => 'test video message',
            'type'  => Post::TYPE_VIDEO_ID,
            'media_url'  => "page/{$page->id}/video/video.mp4"
        ], $photoData);
        $this->assertNotNull($photoData['scheduled_at']);
    }

    public function testGetPublishNowVideoPostUrlInfo()
    {
        list($user, $page) = $this->addUserPage();

        $data = [ 
            'message'  => 'test video message',
            'post_type' => 'video',
            'asset_mode' => 'url',
            'media_url' => 'https://google.com/a.mp4',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertArraySubset([
            'target_url' => '',
            'message'  => 'test video message',
            'type'  => Post::TYPE_VIDEO_ID,
            'media_url'  => "https://google.com/a.mp4"
        ], $photoData);
        $this->assertNotNull($photoData['scheduled_at']);
    }

    public function testGetPostScheduleLaterInfo()
    {
        $page = factory(Page::class)->create();
        $data = [ 
            'post_type' => 'link',
            'post_mode' => 'schedule',
            'date' => '2020-04-13',
            'time_hour' => 10,
            'time_minute' => 0
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
        list($user, $page) = $this->addUserPage();
        $file = UploadedFile::fake()->image('image.jpg');

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
        list($user, $page) = $this->addUserPage();
        $file = UploadedFile::fake()->image('image.jpg');

        $data = [ 
            'post_type' => 'photo',
            'media_url' => 'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
            'save_file' => "true"
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoName = $publishCreateService->getRemoteFile();

        $this->assertEquals("page/{$page->id}/photo/googlelogocolor272x92dp.png", $photoName);
        Storage::disk('local')->assertExists("page/{$page->id}/photo/googlelogocolor272x92dp.png");
    }

    public function testCreatePublishNowLinkInfo()
    {
        list($user, $page) = $this->addUserPage();
        
        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'link',
            'link' => 'https://google.com/a.png',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $photoData = $publishCreateService->getPostInfo();

        $this->assertArraySubset([
            'target_url' => '',
            'message'  => 'test photo message',
            'type'  => Post::TYPE_LINK_ID,
            'link'  => 'https://google.com/a.png'
        ], $photoData);
    }

    public function testCreatePublishNowPhotoPostFileNoCommenReplyInfo()
    {
        Storage::fake('local');
        list($user, $page) = $this->addUserPage();
        $file = UploadedFile::fake()->image('image.png');

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
        $this->assertEquals($post->getOriginal('media_url'), "page/{$page->id}/photo/image.png");
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);
        $this->assertEquals($post->status, Post::STATUS_PROCESSING);
        
        $this->assertEquals($post->comment, null);
        $this->assertEquals($post->reply, null);
    }

    public function testCreatePublishNowPhotoPostFileWithCommenReplyInfo()
    {
        Storage::fake('local');
        list($user, $page) = $this->addUserPage();
        $file = UploadedFile::fake()->image('image.png');

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now',
            'comment' => 'This is the comment',
            'reply_message' => 'This is the reply message',
            'target_url' => 'Loc Nguyen'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $post = $publishCreateService->save();

        $this->assertEquals($post->message, 'test photo message');
        $this->assertEquals($post->type, Post::TYPE_PHOTO_ID);
        $this->assertEquals($post->getOriginal('media_url'), "page/{$page->id}/photo/image.png");
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);
        $this->assertEquals($post->status, Post::STATUS_PROCESSING);

        $this->assertEquals($post->comment->message, 'This is the comment');
        $this->assertEquals($post->comment->status, Comment::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->comment->user_id, $user->id);
        $this->assertEquals($post->comment->post_id, $post->id);

        $this->assertEquals($post->reply->message, 'This is the reply message');
        $this->assertEquals($post->reply->status, Reply::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->reply->user_id, $user->id);
        $this->assertEquals($post->reply->page_id, $page->id);
        $this->assertEquals($post->reply->post_id, $post->id);
        $this->assertEquals($post->reply->fb_target_id, 'Loc Nguyen');
    }

    public function testGePostLinkFile()
    {
        Storage::fake('local');
        list($user, $page) = $this->addUserPage();

        $data = [ 
            'post_type' => 'link'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $path = $publishCreateService->getFileUpload();

        $this->assertEquals($path, "");
    }

    public function testGetRemoteNoneSaveFile()
    {
        list($user, $page) = $this->addUserPage();

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'media_url' => 'http://www.facebook.com/image.png',
            'save_file' => "false"
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $path = $publishCreateService->getRemoteFile();

        $this->assertEquals($data['media_url'], $path);
    }

    public function testPublishLinkNow()
    {
        Queue::fake();

        list($user, $page) = $this->addUserPage();
        
        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'link',
            'link' => 'http://www.facebook.com',
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $post = $publishCreateService->process();

        Queue::assertPushed(SchedulePost::class, 1);
    }

    public function testGetPostNoneTypeInfo()
    {
        list($user, $page) = $this->addUserPage();
        $data = [ 
            'message'  => 'test photo message',
            'target_url' => 'Loc Nguyen'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Invalid data");

        $publishCreateService = new PublishCreateService($data, $page);
        $info = $publishCreateService->getPostInfo();
    }

    public function testGetInvalidScheduleInfo()
    {
        list($user, $page) = $this->addUserPage();
        $data = [ 
            'post_type' => 'link',
            'message'  => 'test photo message',
            'target_url' => 'Loc Nguyen',
            'post_mode' => 'schedule',
            'date'  => '2800-asd-re',
            'time_hour' => 0,
            'time_minute' => 0
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $info = $publishCreateService->getPostScheduleInfo();

        $this->assertEquals([], $info);
    }

    public function testGetNoneAssetModeMediaInfo()
    {
        list($user, $page) = $this->addUserPage();
        $data = [ 
            'post_type' => 'link',
            'message'  => 'test photo message',
            'target_url' => 'Loc Nguyen'
        ];

        $publishCreateService = new PublishCreateService($data, $page);
        $info = $publishCreateService->getPostMediaInfo();

        $this->assertEquals([], $info);
    }

    public function addUserPage()
    {
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $page = factory(Page::class)->create();

        return [$user, $page];
    }

}
