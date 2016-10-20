<?php

namespace Websecret\EventSourceable;

use Auth;

trait EventSourceableTrait
{

    public static $eventTypeCreate = 'create';
    public static $eventTypeUpdate = 'update';
    public static $eventTypeDelete = 'delete';


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
        if($this->exists) {
            $eventType = $this->wasRecentlyCreated ? self::$eventTypeCreate : self::$eventTypeUpdate;
        } else {
            $eventType = self::$eventTypeDelete;
        }
        $userId = Auth::user() ? Auth::user()->id : null;
        $ignore = $this->getIgnore();
        if($eventType == self::$eventTypeDelete) {
            $dirty = $this->getAttributes();
        } else {
            $dirty = $this->getDirty();
            $dirty = array_except($dirty, $ignore);
        }
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
        $events = $this->events()->orderBy('created_at')->get();
        foreach ($events as $event) {
            $this->forceFill($event->diff);
        }
        $this->save();
    }

    public function getIgnore()
    {
        $ignore = $this->ignore();
        if ($this->ignoreDates()) {
            $ignore = array_merge($ignore, $this->getDates());
        }
        if ($this->ignorePrimaryKey()) {
            $ignore[] = $this->primaryKey;
        }
        return $ignore;
    }
}