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

}
