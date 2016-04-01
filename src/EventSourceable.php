<?php

namespace Websecret\EventSourceable;

trait EventSourceable
{
    public function saveDiff()
    {
        $eventType = $this->wasRecentlyCreated ? : 'create' : 'update';
        
        $this->events()->create([
            'diff'   => $this->getDirty(),
            'type'   => $eventType,
        ]);
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
