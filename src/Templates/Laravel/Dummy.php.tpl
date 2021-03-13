<?php
namespace App\Models;

use App\Logic\Helpers\Traits\BootableTrait;
use Illuminate\Database\Eloquent\Model;

class Dummy extends Model {

    use BootableTrait;

    const AVAILABLE_SELECTIONS = [
        "selection",
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'created_by',
        'updated_by',
    ];

	protected $fillable = [];
    protected $casts = [];

    /*public function hasOneRelation()
    {
        return $this->hasOne(Related::class, 'related.id', 'dummy.related_id');
    }
    public function hasManyRelation()
    {
        return $this->hasMany(Related::class, 'dummy.related_id', 'related.id');
    }
    public function belongsToRelation()
    {
        return $this->belongsTo(Related::class, 'dummy.related_id', 'related.id');
    }
    public function belongsToManyRelation()
    {
        return $this->belongsToMany(Related::class, 'intermediate_table')->withPivot('attribute');
    }*/
}
