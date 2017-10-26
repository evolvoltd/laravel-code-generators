<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
class Dummy extends Model {

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

    //public function relation(){
        //cheatsheet: https://hackernoon.com/eloquent-relationships-cheat-sheet-5155498c209
        //return $this->hasOne('App\RelatedModelName', 'foreign_key', 'local_key');
        //return $this->hasMany('App\RelatedModelName', 'foreign_key', 'local_key');
        //return $this->hasManyThrough('App\RelatedModelName', 'intermediate_table', 'intermediate_table_first_key','intermediate_table_second_key','local_key','second_local_key');
        //return $this->belongsTo('App\RelatedModelName','foreign_key','owner_key','relation');
        //return $this->belongsToMany('App\RelatedModelName','table','foreign_pivot_key','related_pivot_key','parent_key','related_key','relation');
    //}

    protected static function boot() {
        parent::boot();

        static::creating(function($table)  {
            $table->created_by = (Auth::check())?Auth::user()->id:1;
            $table->updated_by = (Auth::check())?Auth::user()->id:1;
        });

        static::updating(function($table)  {
            $table->updated_by = Auth::user()->id;
        });

        static::saving(function($table)  {
            $table->updated_by = (Auth::check())?Auth::user()->id:1;
        });
        static::deleting(function($table)  {
            //$table->relation()->delete();
        });
    }

    static function validation($data){

        $rules = [];

        $validation = Validator::make($input = $data, $rules);

        //$validation->sometimes('attribute', 'not_exists:dummy,title', function ($input) {
        //    return Auth::user()->role=='admin';
        //});

        return $validation;
    }


}
