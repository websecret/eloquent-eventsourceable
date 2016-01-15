<?php

namespace Websecret\EventSourceable;

use Websecret\EventSourceable\Models\Event;

trait EventSourceable
{
    /**
     * Save diff between model states into DB
     */
    public function saveDiff()
    {
        $oldState = $this->exists
            ? $this->fresh()->getAttributes()
            : [];
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