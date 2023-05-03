<?php
namespace Evolvo\LaravelCodeGenerators\Converters;

use Illuminate\Support\Str;

class LaravelConverter
{
    public static function convertDatabaseColumnTypeToValidationRule($attribute) : string
    {
        $column_type=  $attribute->Type;
        $field_name = $attribute->Field;
        $validationRules = "";


        if (strstr($column_type, 'int') != false) {
            $validationRules = "integer|max:4294967295";
            if (Str::endsWith($field_name, '_id'))
                $validationRules = "integer|exists:" . substr($field_name, 0, -3) . "s,id";
            if (strstr($column_type, 'tinyint(1)') != false)
                $validationRules = "boolean";
        }
        if (strstr($column_type, 'decimal') != false || strstr($column_type, 'float') != false || strstr($column_type, 'double'))
            $validationRules = "numeric|between:0.01,999999";
        if(strstr($column_type,'varchar')!=false)
            $validationRules = "string|max:150";
        if(strstr($column_type,'text')!=false)
            $validationRules = "string|max:50000";
        if (strstr($column_type, 'datetime') != false)
            $validationRules = "date_format:Y-m-d H:i:s";
        if (strstr($column_type, 'date') != false)
            $validationRules = "date_format:Y-m-d";
        if (strstr($column_type, 'timestamp') != false)
            $validationRules = "date_format:Y-m-d H:i:s";

        return '"' . $attribute->Field . '" => "'.($attribute->Null==='YES'?'nullable':'required').'|' . $validationRules . '"';
    }

    public static function convertDatabaseColumnTypeToFakerFunction($attribute) : string
    {
        $column_type = $attribute->Type;
        $prefix = '$this->faker->';
        $fakerFunction = '';
        if (strstr($column_type, 'int') != false) {
            $fakerFunction = $prefix . "numberBetween(0,1000)";
            if (Str::endsWith($attribute->Field, '_id'))
                $fakerFunction =  (new LaravelConverter())->convertRelatedDatabaseColumnToLaravelModelName($attribute->Field) . '::factory()';
            if (strstr($column_type, 'tinyint(1)') != false)
                $fakerFunction =  $prefix."boolean";
        }
        if (strstr($column_type, 'decimal') != false || strstr($column_type, 'float') != false || strstr($column_type, 'double') != false)
            $fakerFunction =  $prefix."randomFloat(2,0.01,999999)";
        if(strstr($column_type,'varchar')!=false)
            $fakerFunction =  'Str::random()';
        if(strstr($column_type,'text')!=false)
            $fakerFunction =  $prefix."sentence";
        if (strstr($column_type, 'datetime') != false)
            $fakerFunction =  $prefix."dateTime";
        if (strstr($column_type, 'date') != false)
            $fakerFunction =  $prefix."date";
        if (strstr($column_type, 'timestamp') != false)
            $fakerFunction =  $prefix."dateTime";

        return '"' . $attribute->Field . '" => ' . $fakerFunction;
    }

    public static function convertRelatedDatabaseColumnToLaravelModelName($attribute) : string
    {
        return str_replace('_', '', ucwords(substr($attribute, 0, -3), '_'));
    }

    public static function convertRelatedDatabaseColumnsToModelClassUsages($attributes) : array
    {
        return array_map(function ($attribute){
            return 'use App\\Models\\' . (new LaravelConverter())->convertRelatedDatabaseColumnToLaravelModelName($attribute) . ';';
        }, $attributes);
    }

    public static function convertDatabaseTableNameToModelName($databaseTableName) : string
    {
        return str_replace('_', '', ucwords(Str::singular($databaseTableName), '_'));
    }
}
