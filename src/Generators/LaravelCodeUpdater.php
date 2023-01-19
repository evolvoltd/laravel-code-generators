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

    public function __construct(private $databaseTableName,
                                private $databaseTableColumns)
    {
        $this->modelName = LaravelConverter::convertDatabaseTableNameToModelName($this->databaseTableName);
        $this->modelNamePlural = Str::plural($this->modelName);
    }

    public function updateCode() : void
    {
        $tableAttributes = $this->databaseTableColumns;
        try { // todo: refactor to remove try-catches; also refactor to extract code to external function since it looks very similar for every file
            $modelFileContents = File::get(base_path() . '/app/Models/' . $this->modelName . '.php');
            $insert = '';
            $matches = [];
            preg_match("/(fillable = \[).+?(?=];)/s", $modelFileContents, $matches);
            $search = $matches[0];
            foreach ($tableAttributes as $tableAttribute)
                if (!str_contains($modelFileContents, '"' . $tableAttribute->Field . '"'))
                    $insert .= "\t\t\"" . $tableAttribute->Field . "\",\n";

            if ($insert != '') {
                if (!Str::endsWith($search, "\n")) {
                    $replace = rtrim($search) . ',' . "\n" . $insert;
                } else {
                    $replace = $search . $insert;
                }

                file_put_contents(base_path() . '/app/Models/' . $this->modelName . '.php', str_replace(',,', ',', str_replace($search, $replace, $modelFileContents)));
            }
        } catch (Exception $e) {
            throwException($e);
        }
        $factoryName = $this->modelName . "Factory";
        try {
            $factoryFileContents = File::get(base_path() . '/database/factories/' . $factoryName . '.php');

            $insert = '';
            $matches = [];
            preg_match("/(return \[).+?(?=];)/s", $factoryFileContents, $matches);
            $search = $matches[0];

            foreach ($tableAttributes as $tableAttribute)
                if (!str_contains($factoryFileContents, '"' . $tableAttribute->Field . '"'))
                    $insert .= "\t\t\t" . LaravelConverter::convertDatabaseColumnTypeToFakerFunction($tableAttribute).  ",\n";

            if ($insert != '') {
                if (!Str::endsWith($search, "\n")) {
                    $replace = rtrim($search) . ',' . "\n" . $insert;
                } else {
                    $replace = $search . $insert;
                }
                file_put_contents(base_path() . '/database/factories/' . $factoryName . '.php', str_replace(',,', ',', str_replace($search, $replace, $factoryFileContents)));
            }
        } catch (Exception $e) {
        }

        try {
            $requestFiles = File::allFiles(base_path() . '/app/Http/Requests/' . $this->modelName);
            foreach ($requestFiles as $file) {
                $requestFileContents = File::get(base_path() . '/app/Http/Requests/' . $this->modelName . "/" . $file->getRelativePathname());

                $insert = '';
                $matches = [];
                preg_match("/(return \[).+?(?=];)/s", $requestFileContents, $matches);
                $search = $matches[0];

                foreach ($tableAttributes as $tableAttribute)
                    if (!str_contains($requestFileContents, '"' . $tableAttribute->Field . '"')) {
                        $validationRules = LaravelConverter::convertDatabaseColumnTypeToValidationRule($tableAttribute);
                        if(Str::startsWith($file->getRelativePathname(), ['Store', 'Update']))
                            $insert .= "\t\t\t" . $validationRules . ",\n";
                        else $insert .= "\t\t\t" . str_replace(['required|', 'nullable|'], '', $validationRules) . ",\n";
                    }

                if ($insert != '') {
                    if (!Str::endsWith($search, "\n")) {
                        $replace = rtrim($search) . ',' . "\n" . $insert;
                    } else {
                        $replace = $search . $insert;
                    }

                    file_put_contents(base_path() . '/app/Http/Requests/' . $this->modelName . "/" . $file->getRelativePathname(), str_replace(',,', ',', str_replace($search, $replace, $requestFileContents)));
                }
            }
        } catch (Exception $e) {
        }
    }
}
