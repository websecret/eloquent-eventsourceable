<?php

namespace Websecret\EventSourceable;

use Auth;

trait EventSourceableTrait
{

    public function events()
    {
        $class = config('eventsourceable.model');
        return $this->morphMany($class, 'event_sourceable');
    }

    protected function ignore() {
        return [];
    }

    protected function ignoreDates() {
        return true;
    }

    protected function ignorePrimaryKey() {
        return true;
    }

    public function saveDiff()
    {
        $eventType = $this->wasRecentlyCreated ? 'create' : 'update';
        $userId = Auth::user() ? Auth::user()->id : null;
        $ignore = $this->ignore();
        if($this->ignoreDates()) {
            $ignore = array_merge($ignore, $this->getDates());
        }
        if($this->ignorePrimaryKey()) {
            $ignore[] = $this->primaryKey;
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
