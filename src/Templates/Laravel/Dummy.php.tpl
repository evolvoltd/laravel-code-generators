<?php
namespace App;

use App\Logic\Helpers\Taits\BootableTrait;
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
