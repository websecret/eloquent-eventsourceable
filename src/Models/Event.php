<?php

namespace Websecret\EventSourceable\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes;

    protected $dates = ['created_at', 'updated_at', 'deleted_at'];

    protected $fillable = [
        'diff',
        'type',
        'user_id',
    ];
    
    protected $casts = ['diff' => 'array'];

    public function eventSourceable()
    {
        return $this->morphTo('event_sourceable');
    }
}