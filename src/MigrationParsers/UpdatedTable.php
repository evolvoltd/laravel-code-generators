<?php

namespace Evolvo\LaravelCodeGenerators\MigrationParsers;

class UpdatedTable
{
    public array $addedColumns;

    public function __construct(public string $tableName)
    {
        $this->addedColumns = [];
    }


    public function addColumn(object $column){
        if(!array_key_exists($column->Field, $this->addedColumns))
            $this->addedColumns[$column->Field] = $column;
    }
}
