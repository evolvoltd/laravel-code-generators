<?php

namespace Evolvo\LaravelCodeGenerators\Generators;


use Evolvo\LaravelCodeGenerators\Converters\LaravelConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class LaravelCodeUpdater
{
    private string $modelName;
    private string $modelNamePlural;
    private array $relationshipAttributes;
    private array $booleanAttributes;
    private string $insert;
    private array $matches;

    public function __construct(private $databaseTableName,
                                private $databaseTableColumns)
    {
        $this->modelName = LaravelConverter::convertDatabaseTableNameToModelName($this->databaseTableName);
        $this->modelNamePlural = Str::plural($this->modelName);
    }

    public function updateCode(): void
    {
        $tableAttributes = $this->databaseTableColumns;
        // todo: refactor to remove try-catches; also refactor to extract code to external function since it looks very similar for every file
        $modelFileContents = File::get(base_path() . '/app/Models/' . $this->modelName . '.php');
        $this->resetVariables();
        $this->booleanAttributes = [];
        preg_match("/(fillable = \[).+?(?=];)/s", $modelFileContents, $this->matches);
        $search = $this->matches[0];
        $this->getInsertString($tableAttributes, $modelFileContents, 'model');

        if ($this->booleanAttributes != []) {
            $modelFileContents = $this->insertCasts($modelFileContents);
        }

        if ($this->insert != '') {
            $replace = $this->getReplaceString($search);
            if ($this->relationshipAttributes != []) {
                $insertRelationships = '';
                foreach ($this->relationshipAttributes as $relationshipAttribute) {
                    $relationshipName = str::camel($relationshipAttribute->Field);
                    $relationshipName = substr($relationshipName, 0, -2);
                    $insertRelationships .= "\tpublic function " . $relationshipName . "()\n" . "\t{\n"
                        . "\t" . 'return $this->hasOne(' . ucfirst($relationshipName) . "::class, 'id', '" . $relationshipAttribute->Field . "');\n" . "\t}\n\n";
                }
                $modelFileContents = strrev(preg_replace(strrev("/}/"), strrev($insertRelationships . "}"), strrev($modelFileContents), 1));
            }

            file_put_contents(base_path() . '/app/Models/' . $this->modelName . '.php', str_replace(',,', ',', str_replace($search, $replace, $modelFileContents)));
        }


        $factoryName = $this->modelName . "Factory";
        $factoryFileContents = File::get(base_path() . '/database/factories/' . $factoryName . '.php');

        $this->resetVariables();
        preg_match("/(return \[).+?(?=];)/s", $factoryFileContents, $this->matches);
        $search = $this->matches[0];
        $this->getInsertString($tableAttributes, $factoryFileContents, 'factory');

        $modelClassUsages = LaravelConverter::convertRelatedDatabaseColumnsToModelClassUsages($this->relationshipAttributes);

        preg_match("/(namespace).+?(?=;).+?(?=use)/s", $factoryFileContents, $classUsages);

        $insertModelClassUsages = '';
        foreach ($modelClassUsages as $modelClassUsage) {
            $insertModelClassUsages .= $modelClassUsage . "\n";
        }

        $factoryFileContents = str_replace($classUsages[0], $classUsages[0] . $insertModelClassUsages, $factoryFileContents);

        if ($this->insert != '') {
            $replace = $this->getReplaceString($search);
            file_put_contents(base_path() . '/database/factories/' . $factoryName . '.php', str_replace(',,', ',', str_replace($search, $replace, $factoryFileContents)));
        }


        $data = $this->getRequestFiles();
        $requestFiles = $data[0];
        $requestDirectoryName = $data[1];
        /**
         * @var \Symfony\Component\Finder\SplFileInfo $file
         */
        foreach ($requestFiles as $file) {
            if(in_array($file->getFilename(),['Filter.php', 'Store.php', 'Update.php', 'StoreOrUpdate.php'])) {
                $requestFileContents = File::get(base_path() . '/app/Http/Requests/' . $requestDirectoryName . "/" . $file->getRelativePathname());

                $this->resetVariables();
                preg_match("/(return \[).+?(?=];)/s", $requestFileContents, $this->matches);
                $search = $this->matches[0];
                $this->getInsertString($tableAttributes, $requestFileContents, 'request', $file);

                if ($this->insert != '') {
                    $replace = $this->getReplaceString($search);

                    file_put_contents(base_path() . '/app/Http/Requests/' . $requestDirectoryName . "/" . $file->getRelativePathname(), str_replace(',,', ',', str_replace($search, $replace, $requestFileContents)));
                }
            }
        }
    }

    public function getRequestFiles(): array
    {
        $requestFiles = [];
        $requestDirectoryName = '';
        if (file_exists(base_path() . '/app/Http/Requests/' . $this->modelName)) {
            $requestFiles = File::allFiles(base_path() . '/app/Http/Requests/' . $this->modelName);
            $requestDirectoryName = $this->modelName;
        } else if (file_exists(base_path() . '/app/Http/Requests/' . $this->modelName . 's')) {
            $requestFiles = File::allFiles(base_path() . '/app/Http/Requests/' . $this->modelName . 's');
            $requestDirectoryName = $this->modelName . 's';
        } else if (file_exists(base_path() . '/app/Http/Requests/' . $this->modelName . 'es')) {
            $requestFiles = File::allFiles(base_path() . '/app/Http/Requests/' . $this->modelName . 'es');
            $requestDirectoryName = $this->modelName . 'es';
        } else if (file_exists(base_path() . '/app/Http/Requests/' . $this->modelName . 'ies')) {
            $requestFiles = File::allFiles(base_path() . '/app/Http/Requests/' . $this->modelName . 'ies');
            $requestDirectoryName = $this->modelName . 'ies';
        }
        return array($requestFiles, $requestDirectoryName);
    }

    public function getReplaceString(mixed $search): string
    {
        if (!Str::endsWith($search, "\n")) {
            $replace = rtrim($search) . ',' . "\n" . $this->insert;
        } else {
            $replace = $search . $this->insert;
        }
        return $replace;
    }

    public function resetVariables(): void
    {
        $this->insert = '';
        $this->matches = [];
        $this->relationshipAttributes = [];
    }

    public function insertCasts(string $modelFileContents) : string
    {
        $insertCasts = '';
        preg_match("/(casts = \[).*?(?=\];)/s", $modelFileContents, $casts);
        if (!array_key_exists(0, $casts)) {
            preg_match('/(fillable = \[).+?(\];)/s', $modelFileContents, $fillableForCasts);
            $searchFillableForCasts = $fillableForCasts[0];
            $modelFileContents = str_replace($searchFillableForCasts, $searchFillableForCasts . "\n\n\t" . 'protected $casts = [' . "\n" . '];' . "\n", $modelFileContents);
            preg_match("/(casts = \[).+?(?=\];)/s", $modelFileContents, $casts);
        }
        $searchCasts = $casts[0];
        foreach ($this->booleanAttributes as $booleanAttribute) {
            $insertCasts .= "\t" . '"' . $booleanAttribute->Field . '" => "boolean",' . "\n";
        }
        $replaceCasts = rtrim($searchCasts) . ',' . $insertCasts;
        $modelFileContents = str_replace($searchCasts, $replaceCasts, $modelFileContents);
        $modelFileContents = str_replace('[,', '[', $modelFileContents);
        return $modelFileContents;
    }

    public function getInsertString($tableAttributes, string $fileContents, string $type, $file = null): void
    {
        switch($type){
            case('model'):
                foreach ($tableAttributes as $tableAttribute)
                    if (!str_contains($fileContents, '"' . $tableAttribute->Field . '"')) {
                        $this->insert .= "\t\t\"" . $tableAttribute->Field . "\",\n";
                        if (str_ends_with($tableAttribute->Field, '_id')) {
                            $this->relationshipAttributes[] = $tableAttribute;
                        }
                        if ($tableAttribute->Type == "tinyint(1)") {
                            $this->booleanAttributes[] = $tableAttribute;
                        }
                    };
                break;
            case('factory'):
                foreach ($tableAttributes as $tableAttribute)
                    if (!str_contains($fileContents, '"' . $tableAttribute->Field . '"')) {
                        $this->insert .= "\t\t\t" . LaravelConverter::convertDatabaseColumnTypeToFakerFunction($tableAttribute) . ",\n";
                        if (str_ends_with($tableAttribute->Field, '_id'))
                            $this->relationshipAttributes[] = $tableAttribute->Field;
                    }
                break;
            case('request'):
                foreach ($tableAttributes as $tableAttribute)
                    if (!str_contains($fileContents, '"' . $tableAttribute->Field . '"')) {
                        $validationRules = LaravelConverter::convertDatabaseColumnTypeToValidationRule($tableAttribute);
                        if (Str::startsWith($file->getRelativePathname(), ['Store', 'Update']))
                            $this->insert .= "\t\t\t" . $validationRules . ",\n";
                        else $this->insert .= "\t\t\t" . str_replace(['required|', 'nullable|'], '', $validationRules) . ",\n";
                    }
                break;
        }
    }
}
