<?php

namespace Websecret\EventSourceable;

trait EventSourceable
{
    /**
     * Saves diff between model's states into DB
     */
    public function saveDiff()
    {
        $oldState = $this->fresh()->getAttributes();
        $newState = $this->getAttributes();

        $diff = array_diff_assoc($oldState, $newState);
        dd($diff);
    }
}