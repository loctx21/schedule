<?php

namespace App;

use App\Helper\Utils;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    const CONV_INDEX_ENABLED = 1;
    const CONV_INDEX_DISABLED = 0;
    
    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected $fillable = ['name','fb_id','def_fb_album_id','access_token','timezone',
        'message_reply_tmpl','post_reply_tmpl','schedule_time', 'conv_index'];

    protected $appends = ['schedule_option', 'timezone_gmt'];

    /**
     * The users that manage this page
     */
    public function users() 
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }

    public function posts() 
    {
        return $this->hasMany('App\Post');
    }

    /**
     * Get Schedule options
     * 
     * @return array
     */
    public function getScheduleOptionAttribute()
    {
        if (empty($this->schedule_time))
            return [];

        $data =  collect(explode(',', $this->schedule_time));
        return $data->map(function ($time) {
            $timeData = explode(':', $time);
            return [
                'h' => $timeData[0],
                'm' => $timeData[1] 
            ];
        })->toArray();
    }

    /**
     * Get page timezone string
     * 
     * @return string
     */
    public function getTimezoneGmtAttribute() 
    {
        $timezones = Utils::getTimezoneArr();
        foreach ($timezones as $timezone) {
            if ($timezone['zone'] == $this->timezone)
                return $timezone['diff_from_GMT'];
        }
        return '';
    }

    /**
     * Get page image directory path
     * 
     * @return string
     */
    public function getImageDirPath()
    {
        return "page/{$this->id}/photo";
    }

    /**
     * Get page image directory path
     * 
     * @return string
     */
    public function getVideoDirPath()
    {
        return "page/{$this->id}/video";
    }
}
