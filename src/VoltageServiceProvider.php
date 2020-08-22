<?php

namespace Cegrent\Voltage;

use Illuminate\Support\ServiceProvider;

class VoltageServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
      $this->publishes([__DIR__.'/Config/config.php' => config_path('voltage.php')]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
      $this->app->bind('Voltage',function(){
          return new \Cegrent\Voltage\VoltageAPI;
      });
    }
}
