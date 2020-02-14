<?php

namespace Krnos\Fire;

trait HasOperations
{
    /**
     * Get all of the agent's operations.
     */
    public function operations()
    {
        return $this->morphMany(Change::class, 'user');
    }
}