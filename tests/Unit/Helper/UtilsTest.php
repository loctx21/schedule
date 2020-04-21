<?php

namespace Tests\Unit\Helper;

use App\Helper\Utils;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UtilsTest extends TestCase
{

    public function testNextFileNameNoneExist()
    {
        Storage::fake('local');

        $newName = Utils::getNextFileName('/page', "image.png");
        $this->assertEquals($newName, "image.png");
    }

    public function testNextFileNameExisted()
    {
        Storage::fake('local');
        $file = UploadedFile::fake()->image('image.png');
        Storage::putFileAs('/page', $file, 'image.png');

        $newName = Utils::getNextFileName('/page', "image.png");
        $this->assertEquals("image_0.png", $newName);
    }
}
