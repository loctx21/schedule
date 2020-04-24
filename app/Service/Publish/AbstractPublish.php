<?php

namespace App\Service\Publish;

use App\Helper\Utils;
use App\Jobs\SchedulePost;
use App\Page;
use App\Post;
use App\Reply;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

abstract class AbstractPublish
{
    /**
     * Post to updated
     * 
     * @var \App\Post;
     */
    protected $post;

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
     * @return void
     */
    public function __construct($data)    
    {
        if (!array_key_exists('post_type', $data))
            throw new \Exception('Invalid data');

        $this->data = $data;
        if (array_key_exists("save_file", $data))
            $this->data["save_file"] = $data["save_file"] === "true" ? true : false;
    }

    /**
     * Save post, reply, comment information
     * 
     * @return \App\Post
     */
    public function save()
    {
        $this->savePostInfo();

        $this->saveComment();
        $this->saveReply();

        return $this->post;
    }

    /**
     * Save and return post related comment
     * 
     * @return \App\Comment|null
     */
    abstract protected function saveComment();

    /**
     * Save and return post related conversation reply
     * 
     * @return \App\Reply|null
     */
    abstract protected function saveReply();

    public function process()
    {
        $post = $this->save();

        if ($this->data['post_mode'] == 'now') {
            SchedulePost::dispatch($post->id);

            if (config('queue.default') == "sync")
                $post = $post->fresh();
        }

        return $post;
    }

    /**
     * Get post information from attribute data
     * 
     * @return array
     */
    public function getPostInfo()
    {
        $ret = [
            'message' => $this->data['message'],
            'target_url' => array_key_exists('target_url', $this->data) ? $this->data['target_url'] : '',
            'user_id' => Auth::user()->id,
            'page_id' => $this->page->id
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
        }

        if ($this->data['post_type'] != "link")
            $ret = array_merge($ret, $this->getPostMediaInfo());

        $ret = array_merge($ret, $this->getPostScheduleInfo());

        return $ret;
    }

    /**
     * Save post information, update non fillable data
     * 
     * @return void
     */
    protected function savePostInfo()
    {
        $this->post->fill($this->getPostInfo());
        if ($this->data['post_mode'] == 'now')
            $this->post->status = Post::STATUS_PROCESSING;

        $this->post->save();
        $this->post = $this->post->fresh();
        $this->post->scheduled_at_tz = $this->post->getScheduledAtTimezone($this->page->timezone);
    }

    /**
     * Get post schedule information
     * 
     * @return array
     */
    public function getPostScheduleInfo()
    {
        if ($this->data['post_mode'] == 'now') {
            return [
                'scheduled_at' => Carbon::now()->format(Utils::DATETIMEFORMAT)
            ];
        }
        
        $time_hour = $this->data['time_hour'] >= 10 ?  $this->data['time_hour'] : "0" . $this->data['time_hour'];
        $time_minute = $this->data['time_minute'] >= 10 ?  $this->data['time_minute'] : "0" . $this->data['time_minute'];

        $timezone   = new DateTimeZone($this->page->timezone);
        $dateStr = $this->data['date']. ' ' . $time_hour . ':' . $time_minute;
        
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
            return $this->data['media_url'];

        if (!($this->data['save_file']))
            return $this->data['media_url'];

        $path       = pathinfo($this->data['media_url']);
        $realName   = explode('?', $path['basename'])[0];
        
        switch ($this->data['post_type']) {
            case 'photo':
                return $this->getFileContentFromRemote($this->page->getImageDirPath(), $realName);

            case 'video':

                //Let assume we have to check the file manually
                //Fix Google video case
                if (count(explode('.',$realName)) == 1)
                    $realName .= ".mp4";
                return $this->getFileContentFromRemote($this->page->getVideoDirPath(), $realName);
        }

        return ""; 
    }

    /**
     * Save File from remote to storage and return storage path
     * 
     * @param string $path
     * @param string $name
     * 
     * @return string
     */
    public function getFileContentFromRemote($path, $name)
    {
        $realName = Utils::getNextFileName($path, $name);
        $content = file_get_contents($this->data['media_url']);
        Storage::put($path . '/' . $realName, $content);

        return $path . '/' . $realName;
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
                Storage::putFileAs($this->page->getImageDirPath(), $this->data['post_file'], $realName);
                return $final_path;

            case 'video':
                $realName = Utils::getNextFileName($this->page->getVideoDirPath(), $this->data['post_file']->getClientOriginalName());
                $final_path = $this->page->getVideoDirPath() . '/' .$realName;
                Storage::putFileAs($this->page->getVideoDirPath(), $this->data['post_file'],  $realName);
                return $final_path;
        }

        return "";
    }

}