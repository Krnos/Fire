<?php

namespace Krnos\Fire;

use Krnos\Fire\Change;
use Krnos\Fire\Events\FireEvent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ChangeObserver
{
    /**
    * Listen to the Model created event.
    *
    * @param  mixed $model
    * @return void
    */
    public function created(Model $model)
    {   
        if(!static::filter('created')) return;
        
        broadcast(new FireEvent($model, Change::TYPE_CREATED, trans('krnos::fire.created', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]), static::getChangesForSubject($model, Change::TYPE_CREATED), auth()->check() ? auth()->user() : null))->toOthers();
        
    }
    
    /**
     * Listen to the Model updated event.
     *
     * @param  mixed $model
     * @return void
     */
    public function updated(Model $model)
    {   
        if(!static::filter('updated')) return;
        if (Schema::hasColumn($model->getTable(), 'deleted_at')) {
            if($model->isDirty($model->getDeletedAtColumn()) && count($model->getDirty()) == 1) return;
        }

        broadcast(new FireEvent($model, Change::TYPE_UPDATED, trans('krnos::fire.updated', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]), static::getChangesForSubject($model, Change::TYPE_UPDATED), auth()->check() ? auth()->user() : null))->toOthers();
        
    }
    
    /**
     * Listen to the Model deleted event.
     *
     * @param  mixed $model
     * @return void
     */
    public function deleting(Model $model)
    {   
        if(!static::filter('deleting')) return;
        
        broadcast(new FireEvent($model, Change::TYPE_DELETED, trans('krnos::fire.deleted', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]), static::getChangesForSubject($model, Change::TYPE_DELETED), auth()->check() ? auth()->user() : null))->toOthers();
        
    }
    
    /**
     * Listen to the Model restored event.
     *
     * @param  mixed $model
     * @return void
     */
    public function restored(Model $model)
    {   
        if(!static::filter('restored')) return;

        broadcast(new FireEvent($model, Change::TYPE_RESTORED, trans('krnos::fire.restored', ['model' => static::getModelName($model), 'label' => $model->getModelLabel()]), static::getChangesForSubject($model, Change::TYPE_RESTORED), auth()->check() ? auth()->user() : null))->toOthers();

    }

    public static function getModelName(Model $model)
    {
        $class = class_basename($model);
        $key = 'krnos::fire.models.'.Str::snake($class);
        $value =  trans($key);

        return $key == $value ? $class : $value;
    }

    public static function getUserID()
    {
        return auth()->check() ? auth()->user()->id : null;
    }

    public static function getUserType()
    {
        return auth()->check() ? get_class(auth()->user()) : null;
    }

    public static function isIgnored(Model $model, $key)
    {
        $blacklist = config('fire.attributes_blacklist');
        $name = get_class($model);
        $array = isset($blacklist[$name])? $blacklist[$name]: null;
        return !empty($array) && in_array($key, $array);
    }

    public static function filter($action)
    {
        if(!auth()->check()) {
            if(in_array('nobody', config('fire.user_blacklist'))) {
                return false;
            }
        }
        elseif(in_array(get_class(auth()->user()), config('fire.user_blacklist'))) {
            return false;
        }

        return is_null($action) || in_array($action, config('fire.events_whitelist'));
    }

        /**
     * Get the changes for the given model.
     *
     * @param Model $subject
     * @param       $type
     *
     * @return array
     */
    public function getChangesForSubject(Model $subject, $type)
    {
        $before = [];
        $after = [];

        switch ($type) {
            case Change::TYPE_RESTORED:
                $after = $subject->getAttributes();
                break;
            case Change::TYPE_DELETED:
                $before = $subject->getAttributes();
                break;
            case Change::TYPE_UPDATED:
                foreach ($subject->getAttributes() as $key => $afterValue) {
                    if(static::isIgnored($subject, $key)) continue;
                    $beforeValue = $subject->getOriginal($key);

                    if (! config('fire.record_timestamps', false)) {
                        if ($key === $subject->getCreatedAtColumn()) {
                            continue;
                        }
                        if ($key === $subject->getUpdatedAtColumn()) {
                            continue;
                        }
                    }

                    if ($beforeValue !== $afterValue) {
                        Arr::set($before, $key, $beforeValue);
                        Arr::set($after, $key, $afterValue);
                    }
                }

                break;
        }
        return compact('before', 'after');
    }
    
}
