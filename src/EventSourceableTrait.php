<?php

namespace Websecret\EventSourceable;

use Auth;

trait EventSourceableTrait
{

    protected $eventsourceableIngore = [];
    protected $eventsourceableIngoreDates = true;

    public function events()
    {
        $class = config('eventsourceable.model');
        return $this->morphMany($class, 'event_sourceable');
    }

    public function saveDiff()
    {
        $eventType = $this->wasRecentlyCreated ? 'create' : 'update';
        $userId = Auth::user() ? Auth::user()->id : null;
        $ignore = $this->eventsourceableIngore;
        if($this->eventsourceableIngoreDates) {
            $ignore = array_merge($ignore, $this->getDates());
        }
        $dirty = array_except($this->getDirty(), $ignore);
        if(count($dirty)) {
            $this->events()->create([
                'diff' => $dirty,
                'type' => $eventType,
                'user_id' => $userId,
            ]);
        }
    }

    public function rebuild()
    {
        $newModel = new static;
        $events = $this->events;
        foreach ($events as $event) {
            $newModel->fill($event->diff);
        }
        $this->fill($newModel->getAttributes());
        $this->save();
    }
}
