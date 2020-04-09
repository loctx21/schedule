<?php

namespace App\Helper;

use Facebook\PersistentData\PersistentDataInterface;

class FacebookPersistentDataHandler implements PersistentDataInterface
{

    public function __construct()
    {
        
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return session($key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        session([
            $key => $value
        ]);
    }
}