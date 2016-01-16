<?php

namespace Websecret\EventSourceable;

trait EventSourceable
{
    private $oldState  = null;
    private $eventType = null;

    public function onSaving()
    {
        $this->eventType = $this->exists
            ? 'update'
            : 'create';

        $this->oldState = $this->exists
            ? $this->fresh()->getAttributes()
            : [];
    }

    public function onSaved()
    {
        $oldState = $this->oldState;
        $newState = $this->getAttributes();

        $diff = array_diff_assoc($newState, $oldState);
        $this->events()->create([
            'diff'   => $diff,
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