<?php

namespace Websecret\EventSourceable\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'diff',
        'type',
    ];
    protected $casts = ['diff' => 'array'];

    public function eventSourceable()
    {
        return $this->morphTo('event_sourceable');
    }
}