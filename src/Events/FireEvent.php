 
<?php

namespace Krnos\Fire\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class FireEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $model;

    public $name_model;

    public $change_type;
    
    public $message;

    public $changes;

    public $user;

    /**
     * Create a new event instance.
     *
     * @param  Model  $model
     * @param  string  $change_type
     * @param  string  $message
     * @param  array  $changes
     * 
     * @return void
     */
    public function __construct(Model $model, $change_type, $message, $changes = null, $user = null)
    {
        $this->model = $model;
        $this->name_model = class_basename($model);
        $this->change_type = $change_type;
        $this->message = $message;
        $this->changes = $changes;
        $this->user = $user;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new Channel('Fire'.$this->name_model);
    }

}