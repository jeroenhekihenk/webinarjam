<?php

namespace Jeroenhekihenk\Webinarjam;

use App;
use Illuminate\Support\ServiceProvider;

class WebinarJamServiceProvider extends ServiceProvider
{
    protected $defer = false;

    public function boot()
    {
        $this->app->bind(WebinarJam::class, function() {
            return new WebinarJam(new JandjeWebinarJam());
        });
    }

    public function register()
    {
        $this->publishes([
            __DIR__.'config/webinarjam.php' => config_path('webinarjam.php'),
        ]);
        
        $this->app->bind('webinarjam', function() {
            return $this->app->make(WebinarJam::class);
        });
    }
}
