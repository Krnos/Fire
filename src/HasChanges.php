<?php

namespace Krnos\Fire;

trait HasChanges
{
    /**
     * Get all of the model's changes.
     */
    public function changes()
    {
        return $this->morphMany(Change::class, 'model');
    }

    /**
     * Get all of the model's changes.
     *
     * @return void
     */
    public static function bootHasChanges()
    {
        if(!config('fire.enabled')) {
            return;
        }

        if(in_array(app()->environment(), config('fire.env_blacklist'))) {
            return;
        }

        if(app()->runningInConsole() && !config('fire.console_enabled')) {
            return;
        }

        if(app()->runningUnitTests() && !config('fire.test_enabled')) {
            return;
        }

        static::observe(ChangeObserver::class);
    }

    /**
     * Get the model's label in fire.
     *
     * @return string
     */
    public abstract function getModelLabel();
}