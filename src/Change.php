<?php
/**
 * Copyright: © 2020 Krnos
 * Date: 2020-02-20
 * Time: 17:21
 */
namespace Krnos\Fire;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Model;

class Change extends Model
{
    const TYPE_CREATED = 'created';
    const TYPE_UPDATED = 'updated';
    const TYPE_DELETED = 'deleted';
    const TYPE_RESTORED = 'restored';

    /**
    * Indicates if the model should be timestamped.
    *
    * @var bool
    */
    public $timestamps = false;

    /**
    * The attributes that should be mutated to dates.
    *
    * @var array
    */
    protected $dates = ['recorded_at'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'changes' => 'array'
    ];

    /**
    * The attributes that are not mass assignable.
    *
    * @var array
    */
    protected $guarded = [];

    /**
    * The attributes that should be hidden for arrays.
    *
    * @var array
    */
    protected $hidden = [];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->setTable(config('fire.changes_table'));
        parent::__construct($attributes);
    }

    /**
     * Get the user who performed this record
     */
    public function user()
    {
        return $this->hasUser()? $this->morphTo()->first(): null;
    }

    /**
     * Returns whether or not a user type/id are present.
     *
     * @return bool
     */
    public function hasUser()
    {
        return !empty($this->user_type) && !empty($this->user_id);
    }

    /**
     * Get the model of this record
     */
    public function model()
    {
        return $this->morphTo()->first();
    }

    /**
    * Scope a query to only fetch the changes within the given time.
    *
    * @param Builder         $query
    * @param CarbonInterface $from
    * @param CarbonInterface $to
    *
    * @return Builder
    */
    public function scopeWhereRecordedBetween(Builder $query, CarbonInterface $from, CarbonInterface $to)
    {
        return $query->whereBetween('recorded_at', [$from, $to]);
    }
}