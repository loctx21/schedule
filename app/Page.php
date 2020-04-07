<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    /**
     * The attributes that are mass assignable
     * 
     * @var array
     */
    protected $fillable = ['name','fb_id','def_fb_album_id','access_token','timezone','index_message','message_reply_tmpl','post_reply_tmpl','schedule_time'];

    protected $appends = ['schedule_option'];

    /**
     * The users that manage this page
     */
    public function users()
    {
        return $this->belongsTo('App\User');
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
}
