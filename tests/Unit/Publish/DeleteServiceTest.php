<?php

namespace Tests\Unit\Publish;

use App\Service\Publish\PublishDeleteService;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DeleteServiceTest extends TestCase
{
    public function testDeleteFileOnPathUrl()
    {
        $url = 'http://facebook.com/test.png';

        $service = new PublishDeleteService();
        $service->deleteFileOnPath($url);

        $this->assertTrue(true);
    }

    public function testDeleteFileOnPathFile()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.png');
        Storage::putFileAs('/page/post', $file, 'image.png');

        Storage::disk('local')->assertExists('/page/post/image.png');
        
        $service = new PublishDeleteService();
        $service->deleteFileOnPath('/page/post/image.png');

        Storage::disk('local')->assertMissing('/page/post/image.png');
    }
}
