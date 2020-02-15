<?php

namespace Krnos\Fire\Tests;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Krnos\Fire\HasOperations;

class User extends Authenticatable
{
    use Notifiable, HasOperations;
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = [];
}