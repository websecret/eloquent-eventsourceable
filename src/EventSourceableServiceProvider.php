<?php

namespace Websecret\EventSourceable;

use Illuminate\Support\ServiceProvider;

class EventSourceableServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->registerEvents();
    }

    private function registerEvents()
    {
        $this->app['events']->listen('eloquent.saving*', function ($model) {
            if ($model instanceof EventSourceableInterface) {
                $model->saveDiff();
            }
        });
    }
}