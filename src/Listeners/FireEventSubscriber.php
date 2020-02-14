<?php

namespace Krnos\Fire\Listeners;

use Krnos\Fire\Events\FireEvent;
use Krnos\Fire\ChangeObserver;
use Krnos\Fire\Change;
use Illuminate\Contracts\Queue\ShouldQueue;

class FireEventSubscriber implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  FireEvent  $event
     * @return void
     */
    public function onFireEvent($event)
    {
        if(!ChangeObserver::filter(null)) return;

        $event->model->morphMany(Change::class, 'model')->create([
            'change_type' => $event->change_type,
            'message' => $event->message,
            'changes' => $event->changes,
            'user_id' => ChangeObserver::getUserID(),
            'user_type' => ChangeObserver::getUserType(),
            'recorded_at' => time(),
        ]);

        
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher  $events
     */
    public function subscribe($events)
    {
        $events->listen(
            \Krnos\Fire\Events\FireEvent::class,
            static::class.'@onFireEvent'
        );
    }
}