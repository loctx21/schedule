<?php

namespace App\Providers;
use Facebook\Facebook;
use App\Helper\FacebookPersistentDataHandler;
use Illuminate\Support\ServiceProvider;

class FacebookServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('facebook', function ($app) {
            $persistentDataHandler = new FacebookPersistentDataHandler();
            return new Facebook([
                'app_id'        => config('facebook.app_id'),
                'app_secret'    => config('facebook.app_secret'),
                'default_graph_version' => config('facebook.version'),
                'persistent_data_handler' => $persistentDataHandler
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
