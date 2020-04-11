<?php

namespace App\Service\Publish;

use App\Helper\Utils;
use App\Page;
use App\Post;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Storage;

abstract class AbstractPublish
{
    /**
     * The validated data from request
     * 
     * @var array
     */
    protected $data;

    /**
     * The related page model
     * 
     * @var \App\Page
     */
    protected $page;

    /**
     * Create a new page's publish create or update service
     * 
     * @param array $data
     * @param \App\Page $page
     * @return void
     */
    public function __construct($data,  Page $page)    
    {
        $this->data = $data;
        $this->page = $page;
    }

    /**
     * Save post, reply, comment information
     * 
     * @return \App\Post
     */
    abstract public function save();

    /**
     * Get post information from attribute data
     * 
     * @return array
     */
    public function getPostInfo()
    {
        $ret = [
            'message' => $this->data['message']
        ];

        switch ($this->data['post_type']) {
            case 'photo':
                $ret['type'] = Post::TYPE_PHOTO_ID;
                break;

            case 'video':
                $ret['type'] = Post::TYPE_VIDEO_ID;
                break;

            case 'link':
                $ret['type'] = Post::TYPE_LINK_ID;
                $ret['link'] = $this->data['link'];
                break;

            default: 
                break;
        }

        $ret = array_merge($ret, $this->getPostMediaInfo());
        $ret = array_merge($ret, $this->getPostScheduleInfo());

        return $ret;
    }

    /**
     * Get post schedule information
     * 
     * @return array
     */
    public function getPostScheduleInfo()
    {
        if ($this->data['post_mode'] == 'now')
            return [];
        
        $timezone   = new DateTimeZone($this->page->timezone);
        $dateStr = $this->data['date']. ' ' . $this->data['time_hour'] . ':' . $this->data['time_minute'];
        $publishTime = DateTime::createFromFormat('Y-m-d H:i', $dateStr, $timezone);
        
        if ($publishTime)
            return ['scheduled_at' => date(Utils::DATETIMEFORMAT, $publishTime->getTimestamp())];
        return [];
    }

    /**
     * Get media file path
     * 
     * @return array
     */
    public function getPostMediaInfo()
    {
        if (!array_key_exists('asset_mode', $this->data))
            return [];

        if ($this->data['asset_mode'] == 'url')
            return ['media_url' => $this->getRemoteFile()];

        return ['media_url' => $this->getFileUpload()];
    }

    /**
     * Get remote media file path
     * 
     * @return string
     */
    public function getRemoteFile()
    {
        if (empty($this->data['save_file']))
            return $this->data['url'];

        $path       = pathinfo($this->data['url']);
        $realName   = explode('?', $path['basename'])[0];

        switch ($this->data['post_type']) {
            case 'photo':
                $realName = Utils::getNextFileName($this->page->getImageDirPath(), $realName);
                $img = file_get_contents($this->data['url']);
                Storage::put($this->page->getImageDirPath() . '/' . $realName, $img);
                return $this->page->getImageDirPath() . '/' . $realName;

            case 'video':

                //Let assume we have to check the file manually
                //Fix Google video case
                if (count(explode('.',$realName)) == 1)
                    $realName .= ".mp4";

                $realName   = Utils::getNextFileName($this->page->getVideoDirPath(), $realName);
                $video      = file_get_contents($this->data['url']);
                Storage::put($this->page->getVideoDirPath() . '/' . $realName, $video);
                return $this->page->getVideoDirPath() . '/' . $realName;
        }

        return ""; 
    }

    /**
     * Get uploaded media file path
     * 
     * @return string
     */
    public function getFileUpload()
    {
        $realName = '';

        switch ($this->data['post_type']) {
            case 'photo':
                $realName = Utils::getNextFileName($this->page->getImageDirPath(), $this->data['post_file']->getClientOriginalName());
                $final_path = $this->page->getImageDirPath() . '/' .$realName;
                Storage::putFile($final_path, $this->data['post_file']);
                return $final_path;

            case 'video':
                $realName = Utils::getNextFileName($this->page->getVideoDirPath(), $this->data['post_file']->getClientOriginalName());
                $final_path = $this->page->getVideoDirPath() . '/' .$realName;
                Storage::putFile($final_path, $this->data['post_file']);
                return $final_path;
        }

        return "";
    }
}