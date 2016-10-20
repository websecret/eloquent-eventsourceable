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
        $class = $this->getEventClass();
        return $this->morphMany($class, 'event_sourceable');
    }

    public function relatedEvents($relations)
    {
        $class = $this->getEventClass();
        $eventsIds = $this->relatedEventsId($relations);
        return $class::whereIn('id', $eventsIds);
    }

    protected function relatedEventsId($relations) {
        $class = $this->getEventClass();
        $key = $this->getKey();
        $relationsArray = [];
        $arguments = func_get_args();
        foreach ($arguments as $argument) {
            if (is_array($relations)) {
                $relationsArray = array_merge($relationsArray, $argument);
            } else {
                $relationsArray[] = $relations;
            }
        }
        $eventsIds = [];
        foreach ($relationsArray as $relationName) {
            $relation = $this->{$relationName}();
            $relationClass = $relation->getModel();
            $relationForeignKey = last(explode('.', $relation->getForeignKey()));
            $relationEventsIds = $class::where('event_sourceable_type', '=', get_class($relationClass))->whereRaw('`diff`->"$.' . $relationForeignKey . '" = ' . $key)->pluck('id')->toArray();
            $eventsIds = array_merge($eventsIds, $relationEventsIds);
        }
        return array_unique($eventsIds);
    }

    public function eventsWithRelated($relations)
    {
        $class = $this->getEventClass();
        $relatedIds = $this->relatedEventsId($relations);
        $eventsIds = $this->events()->pluck('id')->toArray();
        $ids = array_merge($relatedIds, $eventsIds);
        $ids = array_unique($ids);
        return $class::whereIn('id', $ids);
    }

    protected function ignore()
    {
        return [];
    }

    protected function ignoreDates()
    {
        return true;
    }

    protected function ignorePrimaryKey()
    {
        return true;
    }

    public function saveDiff()
    {
        if ($this->exists) {
            $eventType = $this->wasRecentlyCreated ? self::$eventTypeCreate : self::$eventTypeUpdate;
        } else {
            $eventType = self::$eventTypeDelete;
        }
        $userId = Auth::user() ? Auth::user()->id : null;
        $ignore = $this->getIgnore();
        if ($eventType == self::$eventTypeDelete) {
            $dirty = $this->getAttributes();
        } else {
            $dirty = $this->getDirty();
            $dirty = array_except($dirty, $ignore);
        }
        if (count($dirty)) {
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

    public function getEventClass()
    {
        return config('eventsourceable.model');
    }
}