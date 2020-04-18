<?php

namespace Tests\Unit\Page;

use App\Http\Requests\CreatePage;
use App\Page;
use App\Service\Page\PageCreateService;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\Passport;
use Mockery;

class CreateServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function testCreatePageSuccess()
    {
        $user = factory(User::class)->create();
        Passport::actingAs($user);

        $fbResponseMock = Mockery::mock();
        $fbResponseMock->shouldReceive('getDecodedBody')->andReturn([
            'access_token' => 'access_token_string'
        ]);
        $fbMock = Mockery::mock('facebook');
        $fbMock->shouldReceive('get')->andReturn($fbResponseMock);
        app()->instance('facebook', $fbMock);

        $data = [
            'name'  => 'Test new page',
            'fb_id' => Page::orderBy('id', 'DESC')->first()->fb_id + 1,
            'def_fb_album_id' => 12345
        ];
        
        $service = new PageCreateService();
        $page = $service->create($data);

        $this->assertNotNull($page->id);
        $this->assertEquals($data['name'], $page->name);
        $this->assertEquals($data['fb_id'], $page->fb_id);
        $this->assertEquals($data['def_fb_album_id'], $page->def_fb_album_id);
        $this->assertEquals('access_token_string', $page->access_token);
        $this->assertEquals(1, $user->pages->count());
    }
}
