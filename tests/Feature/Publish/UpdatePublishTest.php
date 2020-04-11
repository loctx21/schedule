<?php

namespace Tests\Feature\Publish;

use App\Comment;
use App\Page;
use App\Post;
use App\Reply;
use App\Service\Publish\PublishEditService;
use App\User;
use Faker\Generator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\Passport;

class UpdatePublishTest extends TestCase
{
    public function testUpdatePublishNowLink()
    {
        $page = factory(Page::class)->create();
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $orgPost = factory(Post::class)->states('type_photo_url')->create([
            'user_id' => $user->id,
            'page_id' => $page->id
        ]);

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'link',
            'link' => 'https://google.com/a.png',
            'post_mode' => 'now',
            'comment' => 'This is the comment',
            'reply_message' => 'This is the reply message',
            'fb_target_id' => 'Loc Nguyen'
        ];

        $publishEditService = new PublishEditService($data, $page, $orgPost);
        $post = $publishEditService->save();

        $this->assertEquals($post->message, $data['message']);
        $this->assertEquals($post->type, Post::TYPE_LINK_ID);
        $this->assertEquals($post->link, $data['link']);
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);
        
        $this->assertEquals($post->comment->message, $data['comment']);
        $this->assertEquals($post->comment->status, Comment::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->comment->user_id, $user->id);
        $this->assertEquals($post->comment->page_id, $page->id);
        $this->assertEquals($post->comment->post_id, $post->id);

        $this->assertEquals($post->reply->message, $data['reply_message']);
        $this->assertEquals($post->reply->status, Reply::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->reply->user_id, $user->id);
        $this->assertEquals($post->reply->page_id, $page->id);
        $this->assertEquals($post->reply->post_id, $post->id);
        $this->assertEquals($post->reply->fb_target_id, 'Loc Nguyen');
    }

    public function testUpdatePublishNowLinkWithCommentReply()
    {
        $page = factory(Page::class)->create();
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $orgPost = factory(Post::class)->states('type_photo_url')->create([
            'user_id' => $user->id,
            'page_id' => $page->id
        ]);
        $comment = Comment::create([
            'message' => 'random message',
            'user_id' => $user->id,  
            'page_id' => $page->id,
            'post_id' => $orgPost->id
        ]);
        $reply = Reply::create([
            'message' => 'random message',
            'user_id' => $user->id,  
            'page_id' => $page->id,
            'post_id' => $orgPost->id,
            'fb_target_id' => 'Loc Nguyen 1'
        ]);

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'link',
            'link' => 'https://google.com/a.png',
            'post_mode' => 'now',
            'comment' => 'This is the comment',
            'reply_message' => 'This is the reply message',
            'fb_target_id' => 'Loc Nguyen'
        ];

        $publishEditService = new PublishEditService($data, $page, $orgPost);
        $post = $publishEditService->save();

        $this->assertEquals($post->message, $data['message']);
        $this->assertEquals($post->type, Post::TYPE_LINK_ID);
        $this->assertEquals($post->link, $data['link']);
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);
        
        $this->assertEquals($post->comment->id, $comment->id);
        $this->assertEquals($post->comment->message, $data['comment']);
        $this->assertEquals($post->comment->status, Comment::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->comment->user_id, $user->id);
        $this->assertEquals($post->comment->page_id, $page->id);
        $this->assertEquals($post->comment->post_id, $post->id);

        $this->assertEquals($post->reply->id, $reply->id);
        $this->assertEquals($post->reply->message, $data['reply_message']);
        $this->assertEquals($post->reply->status, Reply::STATUS_NOT_PUBLISH);
        $this->assertEquals($post->reply->user_id, $user->id);
        $this->assertEquals($post->reply->page_id, $page->id);
        $this->assertEquals($post->reply->post_id, $post->id);
        $this->assertEquals($post->reply->fb_target_id, 'Loc Nguyen');
    }

    public function testUpdatePublishNowPhoto()
    {
        $page = factory(Page::class)->create();
        $user = factory(User::class)->create();
        $this->actingAs($user);

        $orgPost = factory(Post::class)->states('type_photo_url')->create([
            'user_id' => $user->id,
            'page_id' => $page->id
        ]);
        $comment = Comment::create([
            'message' => 'random message',
            'user_id' => $user->id,  
            'page_id' => $page->id,
            'post_id' => $orgPost->id
        ]);
        $reply = Reply::create([
            'message' => 'random message',
            'user_id' => $user->id,  
            'page_id' => $page->id,
            'post_id' => $orgPost->id,
            'fb_target_id' => 'Loc Nguyen 1'
        ]);

        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.png');

        $data = [ 
            'message'  => 'test photo message',
            'post_type' => 'photo',
            'asset_mode' => 'file',
            'post_file' => $file,
            'post_mode' => 'now'
        ];

        $publishCreateService = new PublishEditService($data, $page, $orgPost);
        $post = $publishCreateService->save();

        $this->assertEquals($post->message, $data['message']);
        $this->assertEquals($post->type, Post::TYPE_PHOTO_ID);
        $this->assertEquals($post->media_url, "page/{$page->id}/photo/image.png");
        $this->assertEquals($post->user_id, $user->id);
        $this->assertEquals($post->page_id, $page->id);
        
        $this->assertEquals($post->comment->id, $comment->id);
        $this->assertEquals($post->reply->id, $reply->id);
    }
}
