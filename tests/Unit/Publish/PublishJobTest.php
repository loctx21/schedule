<?php

namespace Tests\Unit\Publish;

use App\Helper\Utils;
use App\Jobs\SchedulePost;
use App\Page;
use App\Post;
use App\Service\Publish\PublishJobCreationService;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Queue;

class PublishJobTest extends TestCase
{
    use RefreshDatabase;

    public function testScheduleNoneValidPost()
    {
        Queue::fake();
        list($user, $page) = $this->addUserPage();

        $service = new PublishJobCreationService();
        $service->schedule();

        Queue::assertPushed(SchedulePost::class, 0);
    }

    public function testScheduleEnoughJob()
    {
        Queue::fake();
        list($user, $page) = $this->addUserPage();

        for($i = 0; $i < 10; $i++) {
            $post = factory(Post::class)->create([
                'user_id'   => $user->id,
                'page_id'   => $page->id,
                'scheduled_at' => '2020-04-10 07:00:00'
            ]);
        }
        $post = factory(Post::class)->create([
            'user_id'   => $user->id,
            'page_id'   => $page->id,
            'scheduled_at' => Carbon::now()->addYear()->format(Utils::DATETIMEFORMAT)
        ]);

        $service = new PublishJobCreationService();
        $service->schedule();

        Queue::assertPushed(SchedulePost::class, 10);
    }

    public function addUserPage()
    {
        $user = factory(User::class)->create();
        $page = factory(Page::class)->create();
        $page->users()->attach($user->id);

        return [$user, $page];
    }

}
