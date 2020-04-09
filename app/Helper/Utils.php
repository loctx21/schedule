<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Helper;

class Utils {
    const DATETIMEFORMAT = 'Y-m-d H:i:s';
    const DATENAMETIMEFORMAT = 'D Y-m-d H:i:s';
    
    /**
     * Generate unique file name on specific path
     * 
     * @param string $path
     * @param string $originalName
     * 
     * @return string name
     */
    static function getNextFileName($path, $originalName) {
        $fileInfo = pathinfo($originalName);
        $newName = self::getNormalizeName($originalName); 
        $i = 0;
       
		$newFileNamePath = pathinfo($newName);

        while (file_exists($path . '/' . $newName)) {
            $newName = $newFileNamePath['filename'] . "_" . $i . "." . $fileInfo["extension"];
            $i++;
        }
        return $newName;
    }

    /**
     * Normalize name to safe string which can be used on other service
     * 
     * @return string
     */
    static function getNormalizeName($string, $force_lowercase = true, $anal = false) {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ">", "/", "?");
        $clean = trim(str_replace($strip, "", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
        return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                mb_strtolower($clean, 'UTF-8') :
                strtolower($clean) :
            $clean;
    }
    
    /**
     * Get associated timezone 
     * 
     * @return array
     */
    static function getTimezoneArr() 
    {
        $zones_array = array();
        $timestamp = time();
        foreach(timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp) . ' ' . $zone;
        }
        return $zones_array;
    }
    
    /**
     * 
     * @param Post $post
     */
    static function getPostImgUrl($post) {
        $fileInfo = pathinfo($post->media_url);
		if (empty($post->media_url))
			return '';
        if ($fileInfo['dirname'] != '.')
            return $post->media_url;
        else {
            return env('APP_URL') . env('STATIC_FOLDER') . '/' . $post->page_id . '/' . $post->media_url;
        }
    }

    /**
     * Get video url either from remote or local file
     * @param Post $post
     */
    static function getPostVideoUrl($post) {
        $fileInfo = pathinfo($post->media_url);
		if (empty($post->media_url))
			return '';
        if ($fileInfo['dirname'] != '.')
            return $post->media_url;
        else {
            return env('APP_URL') . env('STATIC_FOLDER') . '/' . env('STATIC_VIDEO') . $post->page_id . '/' . $post->media_url;
        }
    }

    static function getPageVideoDirPath($page_id) {
        return public_path('/' . env('STATIC_FOLDER') . '/' . env('STATIC_VIDEO') . $page_id);
    }

    static function getPageImageDirPath($page_id) {
        return public_path('/' . env('STATIC_FOLDER') . '/' . $page_id);

    }


}
