<?php

namespace Tests\Unit\Facebook;

use App\Helper\FacebookPersistentDataHandler;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PersistentDataHandlerTest extends TestCase
{
    public function testSetKeyValue()
    {
        $data  = [
            'test_key' => 'test'
        ];

        $handler = new FacebookPersistentDataHandler();
        $handler->set("test_key", $data["test_key"]);

        $this->assertEquals($data["test_key"], session("test_key"));

    }

    public function testGetValue()
    {
        $data  = [
            'test_key' => 'test'
        ];

        $handler = new FacebookPersistentDataHandler();
        $handler->set("test_key", $data["test_key"]);

        session($data);

        $this->assertEquals($data["test_key"], $handler->get("test_key"));

    }
}
