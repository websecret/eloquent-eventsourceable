<?php

namespace Websecret\EventSourceable;

trait EventSourceable
{
    public function onSaved()
    {
        $this->events()->create([
            'diff'   => $this->getDirty(),
            'type'   => $this->eventType,
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
