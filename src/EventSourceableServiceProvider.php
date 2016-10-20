<?php

namespace Websecret\EventSourceable;

use Illuminate\Support\ServiceProvider;

class EventSourceableServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->handleConfigs();
    }

    public function register()
    {
        $this->registerEvents();
    }

    private function registerEvents()
    {
        $this->app['events']->listen('eloquent.saved*', function ($model) {
            if ($model instanceof EventSourceableInterface) {
                $model->saveDiff();
            }
        });

        $this->app['events']->listen('eloquent.deleted*', function ($model) {
            if ($model instanceof EventSourceableInterface) {
                $model->saveDiff();
            }
        });
    }

    private function handleConfigs()
    {
        $configPath = __DIR__ . '/../config/eventsourceable.php';
        $this->publishes([$configPath => config_path('eventsourceable.php')], 'config');
        $this->mergeConfigFrom($configPath, 'eventsourceable');
    }
}
