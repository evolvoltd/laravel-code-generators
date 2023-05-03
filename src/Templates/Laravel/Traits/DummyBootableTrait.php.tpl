<?php

namespace App\Logic\Helpers\Traits;

trait BootableTrait
{
    public static function bootBootableTrait()
    {
        static::creating(function($table) {
            $table->created_by = (auth()->check()) ? auth()->id() : 1;
            $table->updated_by = (auth()->check()) ? auth()->id() : 1;
        });

        static::updating(function($table) {
            $table->updated_by = (auth()->check()) ? auth()->id() : 1;
        });
    }
}
