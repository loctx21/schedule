<?php

namespace App\Service\Publish;

use App\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PublishDeleteService
{
    /**
     * Delete post and related information
     * 
     * @return void
     */
    public function delete(Post $post)
    {   
        $path = $post->getOriginal('media_url');
        $this->deleteFileOnPath($path);
        $post->delete();
    }

    /**
     * Delete file on path
     * 
     * @param string $path
     * @return void
     */
    public function deleteFileOnPath($path)
    {
        if (strpos($path, "://") !== false)
            return;
        
        if (empty($path))
            return;

        Storage::delete($path);
    }
}
