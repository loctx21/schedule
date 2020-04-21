<?php

namespace Tests\Unit\Reply;

use App\Helper\Utils;
use App\Reply;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ReplyModelTest extends TestCase
{
    public function testExtractDefaultEmptyReplyType()
    {
        $url = "";

        $type = Reply::extractReplyType($url);

        $this->assertEquals(Reply::TYPE_VISITOR_POST, $type);
    }

    public function testExtractVideoReplyType()
    {
        $url = "https://business.facebook.com/abc/videos/667024427041257/";

        $type = Reply::extractReplyType($url);

        $this->assertEquals(Reply::TYPE_VIDEO, $type);
    }

    public function testExtractInboxReplyType()
    {
        $url = "https://business.facebook.com/abc?selected_item_id=1234";

        $type = Reply::extractReplyType($url);

        $this->assertEquals(Reply::TYPE_MESSAGE, $type);
    }

    public function testExtractNameMessageReplyType()
    {
        $url = "Loc Nguyen";

        $type = Reply::extractReplyType($url);

        $this->assertEquals(Reply::TYPE_MESSAGE, $type);
    }

    public function testExtractCommentReplyType()
    {
        $url = "https://www.facebook.com/257408390963565/photos/a.1159410334086795/2334815889879561/?type=3&av=257408390963565&eav=AfYX36t2-AVVF7eKhqSnDn8GZ5-IF43ZdHPMd-w1FMxcS5b91v77RIgvDggxFARObY0&comment_id=3430631320298007";

        $type = Reply::extractReplyType($url);

        $this->assertEquals(Reply::TYPE_PHOTO_COMMENT, $type);
    }

    public function testExtractDefaultReplyType()
    {
        $url = "https://www.facebook.com/257408390963565/photos/a.1159410334086795/2334815889879561/?type=3&av=257408390963565&eav=AfYX36t2-AVVF7eKhqSnDn8GZ5-IF43ZdHPMd-w1FMxcS5b91v77RIgvDggxFARObY0";

        $type = Reply::extractReplyType($url);

        $this->assertEquals(Reply::TYPE_VISITOR_POST, $type);
    }

    //Target Id
    public function testExtractDefaultEmptyReplyTargetId()
    {
        $url = "";

        $type = Reply::extractReplyTargetId($url);

        $this->assertEquals(null, $type);
    }

    public function testExtractVideoReplyTargetId()
    {
        $url = "https://business.facebook.com/abc/videos/667024427041257/";

        $type = Reply::extractReplyTargetId($url);

        $this->assertEquals(667024427041257, $type);
    }

    public function testExtractInboxReplyTargetId()
    {
        $url = "https://business.facebook.com/abc?selected_item_id=1234";

        $type = Reply::extractReplyTargetId($url);

        $this->assertEquals(1234, $type);
    }

    public function testExtractNameMessageReplyTargetId()
    {
        $url = "Loc Nguyen";

        $type = Reply::extractReplyTargetId($url);

        $this->assertEquals("Loc Nguyen", $type);
    }

    public function testExtractCommentReplyTargetId()
    {
        $url = "https://www.facebook.com/257408390963565/photos/a.1159410334086795/2334815889879561/?type=3&av=257408390963565&eav=AfYX36t2-AVVF7eKhqSnDn8GZ5-IF43ZdHPMd-w1FMxcS5b91v77RIgvDggxFARObY0&comment_id=3430631320298007";

        $type = Reply::extractReplyTargetId($url);

        $this->assertEquals("2334815889879561_3430631320298007", $type);
    }

    public function testExtractDefaultReplyTargetId()
    {
        $url = "https://www.facebook.com/photo.php?fbid=1660764580111961&set=o.257408390953665&type=3";

        $type = Reply::extractReplyTargetId($url);

        $this->assertEquals("1660764580111961", $type);
    }
}
