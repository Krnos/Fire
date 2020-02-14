 <?php

namespace Krnos\Fire\Tests;

use Illuminate\Database\Eloquent\Model;
use Krnos\Fire\HasChanges;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes, HasChanges;
    public $timestamps = false;
    protected $guarded = [];
    protected $hidden = [];

    public function getModelLabel()
    {
        return $this->getOriginal('title', $this->title);
    }
}