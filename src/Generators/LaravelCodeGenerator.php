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

class LaravelCodeGenerator
{
    private string $modelName;
    private string $modelNamePlural;
    private array $fillableAttributes;
    private array $searchableAttributes;
    private array $booleanAttributes;
    private array $validationRules;
    private array $factoryAttributes;
    private array $relatedAttributes;

    public function __construct(private $databaseTableName,
                                private $databaseTableColumns)
    {
        $this->prepareData();
        $this->modelName = LaravelConverter::convertDatabaseTableNameToModelName($this->databaseTableName);
        $this->modelNamePlural = Str::plural($this->modelName);
    }

    public function generateLaravelCodeFromNewlyCreatedTable() : void
    {
        $this->createNecessaryDirectories($this->modelName);

        $this->generateModelBootableTraitFile();

        $this->generateModelFile(
            $this->databaseTableName,
            $this->modelName,
            $this->fillableAttributes,
            $this->booleanAttributes,
            $this->searchableAttributes,
            $this->relatedAttributes,
            $this->modelClassUsages
        );

        $this->generateServiceFile(
            $this->modelName,
            $this->modelNamePlural
        );

        $this->generateRequestFiles(
            $this->modelName,
            $this->validationRules
        );

        $this->generateControllerFile(
            $this->modelName,
            $this->modelNamePlural
        );

        $this->appendToApiRoutesFiles(
            $this->databaseTableName,
            $this->modelNamePlural
        );


        $this->generateFactoryFile(
            $this->modelName,
            $this->factoryAttributes,
            $this->modelClassUsages
        );

        $this->generateTestFile($this->databaseTableName);

        $this->generateApiDocumentionFile($this->databaseTableName);
    }

    private function prepareData() : void
    {
        foreach ($this->databaseTableColumns as $column) {
            if (!in_array($column->Field, ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'])) {
                $this->fillableAttributes[] = $column->Field;
                if (preg_match('/name|title/i', $column->Field)) $this->searchableAttributes[] = $column->Field;
                if ($column->Type == 'tinyint(1)') $this->booleanAttributes[] = $column->Field;
                $this->validationRules[] = LaravelConverter::convertDatabaseColumnTypeToValidationRule($column);
                $this->factoryAttributes[] = LaravelConverter::convertDatabaseColumnTypeToFakerFunction($column);
                if (strstr($column->Type, 'int') != false && Str::endsWith($column->Field, '_id')) $this->relatedAttributes[] = $column->Field;
            }
        }
        $this->modelClassUsages = LaravelConverter::convertRelatedDatabaseColumnsToModelClassUsages($this->relatedAttributes);
    }

    private function info($output){
        $out = new \Symfony\Component\Console\Output\ConsoleOutput();
        $formater = new OutputFormatter(true);
        $out->setFormatter($formater);
        $out->writeLn($output);
    }

    private function createNecessaryDirectories($modelName) : void
    {
        $directories = [
            app_path('Models'),
            app_path('Http/Controllers'),
            app_path('Services'),
            app_path('Http/Requests/' . $modelName),
            app_path('Logic/Helpers/Traits'),
            base_path('routes'),
        ];

        foreach ($directories as $directory)
            if (!file_exists($directory))
                mkdir($directory, 0777, true);

    }

    private function generateModelBootableTraitFile() : void
    {

        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/Traits/DummyBootableTrait.php.tpl');
        file_put_contents(app_path('Logic/Helpers/Traits/BootableTrait.php'), $file_contents);
        $this->info("Model bootable trait file generated!");
    }

    private function generateModelFile($databaseTableName,
                                       $modelName,
                                       $fillableAttributes,
                                       $booleanAttributes,
                                       $searchableAttributes,
                                       $relatedAttributes,
                                       $modelClassUsages) : void
    {


        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/Models/Dummy.php.tpl');
        $file_contents = str_replace("Dummy", $modelName, $file_contents);
        $file_contents = str_replace("dummy", $databaseTableName, $file_contents);
        $file_contents = str_replace("SEARCHABLE_ATTRIBUTES = []", 'SEARCHABLE_ATTRIBUTES = [' . PHP_EOL . '        "' . implode('",' . PHP_EOL . '        "', $searchableAttributes) . '"' . PHP_EOL . '    ]', $file_contents);
        $file_contents = str_replace("fillable = []", 'fillable = [' . PHP_EOL . '        "' . implode('",' . PHP_EOL . '        "', $fillableAttributes) . '"' . PHP_EOL . '    ]', $file_contents);
        $file_contents = str_replace("casts = []", 'casts = [' . PHP_EOL . '        "' . implode('" => "boolean",' . PHP_EOL . '        "', $booleanAttributes) . '" => "boolean"' . PHP_EOL . '    ]', $file_contents);
        file_put_contents(app_path('Models/' . $modelName . '.php'), $file_contents);

        if (count($relatedAttributes)) {

            $replacement = '';
            foreach ($relatedAttributes as $relatedAttribute) {

                $foreign_model_name = str_replace('_', '', ucwords(substr($relatedAttribute, 0, -3), '_'));
                $replacement .= "\n" . '    public function ' . lcfirst($foreign_model_name) . '()
    {
        return $this->hasOne(' . $foreign_model_name . '::class,\'id\',\'' . $relatedAttribute . '\');
    }';
            }
            $file_contents = substr($file_contents, 0, -2) . $replacement . "\n}";
        }
        $file_contents = str_replace("//[RELATED_MODEL_IMPORTS]", implode("\n", $modelClassUsages), $file_contents);

        //generate model relations
        $migrationsDir = scandir(base_path('database/migrations'));
        foreach ($migrationsDir as $file) {
            if (strpos($file, 'create_' . $databaseTableName) !== false) {
                $lines = file(base_path('database/migrations/' . $file));
                foreach ($lines as $line) {

                    if (strpos($line, '$table->foreign(') !== false) {
                        $refs = explode('(\'', $line);
                        $foreign = str_replace("')->references", '', $refs[1]);
                        $reference = str_replace("')->on", '', $refs[2]);
                        $foreign_table = str_replace("');\n", '', $refs[3]);
                        $foreign_table = str_replace("')->onDelete", '', $foreign_table);

                        $singular_foreign_table_name = (substr($foreign_table, strlen($foreign_table) - 4, 3) == 'ies') ? (substr($foreign_table, 0, -3) . 'y') : (substr($foreign_table, 0, -1));


                        if (substr($foreign_table, -1) == 's') {
                            $this->info($singular_foreign_table_name);
                            $foreign_model_name = str_replace('_', '', ucwords($singular_foreign_table_name, '_'));
                        } else {
                            $foreign_model_name = str_replace('_', '', ucwords($foreign_table, '_'));
                        }
                        $replacement = "\n" . '    public function ' . lcfirst($foreign_model_name) . '()
    {
        return $this->hasOne(\'App\Models\\' . $foreign_model_name . '\',\'id\',\'' . $foreign . '\');
    }' . "\n}";
                        $file_contents = substr($file_contents, 0, -2) . $replacement;


                        $foreign_model_contents = file_get_contents(app_path('Models/' . $foreign_model_name . '.php'));
                        $replacement = "\n" . '    public function ' . $databaseTableName . '()
    {
        return $this->hasMany(\'App\Models\\' . $modelName . '\',\'' . $foreign . '\',\'id\');
    }' . "\n}";
                        $foreign_model_contents = substr($foreign_model_contents, 0, -2) . $replacement;
                        file_put_contents(app_path('Models/' . $foreign_model_name . '.php'), $foreign_model_contents);
                    }
                }
            }
        }
        file_put_contents(app_path('Models/' . $modelName . '.php'), $file_contents);
        $this->info("Model file generated!");
    }

    private function generateServiceFile($modelName,
                                         $modelNamePlural) : void
    {

        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/DummyService.php.tpl');
        $file_contents = str_replace("Dummy", $modelName, $file_contents);
        $file_contents = str_replace("Dummies", $modelNamePlural, $file_contents);
        $file_contents = str_replace("dummyItem", '$' . lcfirst($modelName), $file_contents);
        file_put_contents(app_path('Services/' . $modelNamePlural . 'Service' . '.php'), $file_contents);
        $this->info("Service file generated!");
    }

    private function generateControllerFile($modelName,
                                            $modelNamePlural) : void
    {

        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/DummyController.php.tpl');
        $file_contents = str_replace("DummyController", $modelNamePlural . 'Controller', $file_contents);
        $file_contents = str_replace("Dummy", $modelName, $file_contents);
        $file_contents = str_replace("dummyService", lcfirst($modelNamePlural) . "Service", $file_contents);
        $file_contents = str_replace("Dummies", $modelNamePlural . "", $file_contents);
        $file_contents = str_replace("dummyItem", '$' . lcfirst($modelName), $file_contents);
        $file_contents = str_replace("ServiceName", $modelNamePlural . 'Service', $file_contents);
        file_put_contents(app_path('Http/Controllers/' . $modelNamePlural . 'Controller' . '.php'), $file_contents);
        $this->info("Controller file generated!");
    }

    private function generateRequestFiles($modelName,
                                          $validationRules) : void
    {

        //generate StoreOrUpdate FormRequest
        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/Requests/Dummy/StoreOrUpdate.php.tpl');
        $file_contents = str_replace("Dummy", $modelName, $file_contents);
        $file_contents = str_replace("return []", 'return [' . PHP_EOL . '            ' . implode(',' . PHP_EOL . '            ', $validationRules) . '' . PHP_EOL . '        ]', $file_contents);
        file_put_contents(app_path('Http/Requests/' . $modelName . '/StoreOrUpdate' . '.php'), $file_contents);

        //generate Filter FormRequest
        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/Requests/Dummy/Filter.php.tpl');
        $file_contents = str_replace("Dummy", $modelName, $file_contents);
        $file_contents = str_replace("return []", 'return [' . PHP_EOL . '            ' . implode(',' . PHP_EOL . '            ', str_replace(['required|', 'nullable|'], '', $validationRules)) . '' . PHP_EOL . '        ]', $file_contents);
        file_put_contents(app_path('Http/Requests/' . $modelName . '/Filter' . '.php'), $file_contents);

        //generate Find FormRequest
        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/Requests/Find.php.tpl');
        file_put_contents(app_path('Http/Requests/Find.php'), $file_contents);
        $this->info("Request files generated!");
    }

    private function appendToApiRoutesFiles($databaseTableName,
                                            $modelNamePlural) : void
    {

        //generate crud route
        $route = str_replace('_', '-', $databaseTableName);
        $file_contents = file_get_contents(base_path('routes/api.php'));
        $file_contents .= "\n" . 'Route::apiResource(\'' . $route . '\', \\App\\Http\\Controllers\\' . $modelNamePlural . 'Controller::class);';

        //generate find route
        $file_contents .= "\n" . 'Route::get(\'' . $route . '/find/{search}' . '\', [\\App\\Http\\Controllers\\' . $modelNamePlural . 'Controller::class, \'find\']);';
        file_put_contents(base_path('routes/api.php'), $file_contents);
        $this->info("Routes appended to API routes file!");
    }

    private function generateFactoryFile($modelName,
                                         $factoryAttributes,
                                         $modelClassUsages) : void
    {

        //generate factory class
        $file_contents = file_get_contents(__DIR__ . '/../Templates/Laravel/DummyFactory.php.tpl');
        $file_contents = str_replace("Dummy", $modelName, $file_contents);
        $file_contents = str_replace("return []", 'return [' . PHP_EOL . '            ' . implode(',' . PHP_EOL . '            ', $factoryAttributes) . '' . PHP_EOL . '        ]', $file_contents);
        $file_contents = str_replace("//modelClassUsages", implode(PHP_EOL, $modelClassUsages) . PHP_EOL, $file_contents);
        file_put_contents(base_path('database/factories/' . $modelName . 'Factory' . '.php'), $file_contents);
        $this->info("Factory file generated!");
    }

    private function generateTestFile($databaseTableName)
    {

        Artisan::call('simple-crud-test',
            [
                'table' => $databaseTableName
            ]
        );

    }

    private function generateApiDocumentionFile($databaseTableName)
    {

        if (array_key_exists('swagger:generate', Artisan::all())) {
            Artisan::call('swagger:generate');
            $this->info("Swagger documentation generated!");
        }

        if (array_key_exists('l5-swagger:generate', Artisan::all())) {
            Artisan::call('generate:swagger',
                [
                    'table' => $databaseTableName
                ]
            );
            Artisan::call('l5-swagger:generate');
            $this->info("Swagger documentation generated!");
        }
    }
}
