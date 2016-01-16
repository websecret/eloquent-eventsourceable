<?php

namespace Websecret\EventSourceable;

use Websecret\EventSourceable\Models\Event;

trait EventSourceable
{
    private $oldState = null;
    /**
     * Save diff between model states into DB
     */
    public function onSaving()
    {
        $oldState = $this->exists
            ? $this->fresh()->getAttributes()
            : [];
        $this->oldState = $oldState;
    }

    public function onSaved()
    {
        $oldState = $this->oldState;
        $newState = $this->getAttributes();

        $diff = array_diff_assoc($oldState, $newState);
        $this->events()->create([
            'diff'   => $diff,
        ]);
    }

    public function rebuild()
    {
        $newModel = new static;
        $events = $this->events;
        foreach ($events as $event) {
            $newModel->fill($events->diff);
        }
        $this->fill($newModel->getAttributes());
        $this->save();
    }
}