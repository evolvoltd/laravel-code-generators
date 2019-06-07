<?php

namespace Evolvo\LaravelCodeGenerators;

use App\Providers\AuthServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class AutoGenerateModelCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold {database_table} {--only-ng} {--no-tr} {--no-t}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto generate all related files model files for backend and front end';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //check if table exists
        $table = $this->argument('database_table');
        if (!DB::getSchemaBuilder()->hasTable($table)) {
            $this->comment(PHP_EOL . 'Table doesn`t exist.' . PHP_EOL);
            exit;
        }

        $columns = DB::select('show columns from ' . $table);
        $fillable_columns = [];
        $boolean_columns = [];
        $validation_rules = [];
        $table_headers = [];
        $table_columns = [];
        $angular_model_attributes = [];
        $form_fields = [];
        $singular_table_name = (substr($table, strlen($table) - 4, 3) == 'ies') ? (substr($table, 0, -3) . 'y') : (substr($table, 0, -1));
        foreach ($columns as $value) {
            if (!in_array($value->Field, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) {
                $fillable_columns[] = $value->Field;

                $validation_rules[] = '"' . $value->Field . '" => "required|' . $this->convertDatabaseColumnTypeToValidationRule($value->Type) . '"';
                $angular_model_attributes[] = $value->Field . ': ' . $this->convertDatabaseColumnTypeToAngularType($value->Type) . ';';
                $table_headers[] = '<th>' . ucfirst($value->Field) . '</th>';
                $table_columns[] = '<td>{{' . $singular_table_name . '.' . $value->Field . '}}</td>';
                $form_fields[] =
                    '<div class="form-group">' . PHP_EOL .
                    '<label>' . ucfirst($value->Field) . '</label>' . PHP_EOL .
                    '<input type="text" class="form-control" [(ngModel)]="' . $singular_table_name . '.' . $value->Field . '" placeholder="' . ucfirst($value->Field) . '">' . PHP_EOL .
                    '</div>';
            }

            if ($value->Type == 'tinyint(1)')
                $boolean_columns[] = $value->Field;
        }
        if (substr($table, -1) == 's')
            $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));
        else
            $model_name = str_replace('_', '', ucwords($table, '_'));

        if ($this->option('only-ng')) {
            $dir = app_path('Console/Commands/Output/Angular/' . $table . '/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-paginated.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $singular_table_name . '-paginated.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummies.component.html');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            $file_contents = str_replace("DUMMY_HEADERS", implode(PHP_EOL, $table_headers), $file_contents);
            $file_contents = str_replace("DUMMY_COLUMNS", implode(PHP_EOL, $table_columns), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $table . '.component.html'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummies.component.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $table . '.component.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy.service.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $table . '.service.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("MODEL_ATTRIBUTES", implode(PHP_EOL, $angular_model_attributes), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $singular_table_name . '.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-detail.component.html');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $singular_table_name . '-detail.component.html'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-detail.component.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $singular_table_name . '-detail.component.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-modal.component.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $singular_table_name . '-modal.component.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-modal.component.html');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            $file_contents = str_replace("FORM_FIELDS", implode(PHP_EOL, $form_fields), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/' . $singular_table_name . '-modal.component.html'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/app.module.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/app.module.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/app-routing.module.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $table . '/app-routing.module.ts'), $file_contents);


        } else {
            //GENERATE BACK-END CODE START
            //generate model
            $dir = app_path('Models/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/Dummy.php.tpl');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $table, $file_contents);
            $file_contents = str_replace("fillable = []", 'fillable = [' . PHP_EOL . '        "' . implode('",' . PHP_EOL . '        "', $fillable_columns) . '"' . PHP_EOL . '    ]', $file_contents);
            $file_contents = str_replace("casts = []", 'casts = [' . PHP_EOL . '        "' . implode('" => "boolean",' . PHP_EOL . '        "', $boolean_columns) . '" => "boolean"' . PHP_EOL . '    ]', $file_contents);
            file_put_contents(app_path('Models/' . $model_name . '.php'), $file_contents);
            //generate model relations
            $migrationsDir = scandir(base_path('database/migrations'));
            foreach ($migrationsDir as $file) {
                if (strpos($file, 'create_' . $table) !== false) {
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
                            }
                            else
                            {
                                $foreign_model_name = str_replace('_', '', ucwords($foreign_table, '_'));
                            }
                            $replacement = "\n".'    public function '.lcfirst($foreign_model_name).'()
    {
        return $this->hasOne(\'App\Models\\'.$foreign_model_name .'\',\'id\',\''.$foreign.'\');
    }'."\n}";
                            $file_contents = substr($file_contents, 0, -2).$replacement;



                            $foreign_model_contents = file_get_contents(app_path('Models/' . $foreign_model_name . '.php'));
                            $replacement = "\n".'    public function '.$table.'()
    {
        return $this->hasMany(\'App\Models\\'.$model_name .'\',\''.$foreign.'\',\'id\');
    }'."\n}";
                            $foreign_model_contents = substr($foreign_model_contents, 0, -2).$replacement;
                            file_put_contents(app_path('Models/' . $foreign_model_name . '.php'), $foreign_model_contents);
                        }
                    }
                }
            }
            file_put_contents(app_path('Models/' . $model_name . '.php'), $file_contents);


            //fill custom translation rules

            $dir = app_path('Services');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            //generate service
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummyService.php.tpl');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummyService", lcfirst($model_name) . "Service", $file_contents);
            $file_contents = str_replace("dummies", lcfirst($model_name) . "s", $file_contents);
            $file_contents = str_replace("dummyItem", '$' . lcfirst($model_name), $file_contents);
            file_put_contents(app_path('Services/' . $model_name . 'Service' . '.php'), $file_contents);


            //generate controller
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummyController.php.tpl');
            $file_contents = str_replace("DummyController", $model_name . 'sController', $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummyService", lcfirst($model_name) . "Service", $file_contents);
            $file_contents = str_replace("dummies", lcfirst($model_name) . "s", $file_contents);
            $file_contents = str_replace("dummyItem", '$' . lcfirst($model_name), $file_contents);
            file_put_contents(app_path('Http/Controllers/' . $model_name . 'sController' . '.php'), $file_contents);


            //generate crud route
            $route = str_replace('_', '-', $table);
            $file_contents = file_get_contents(base_path('routes/api.php'));
            $file_contents .= "\n" . 'Route::apiResource(\'' . $route . '\', \'' . $model_name . 'sController\');';
            file_put_contents(base_path('routes/api.php'), $file_contents);

            $dir = app_path('Http/Requests/' . $model_name . '/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            //generate request class
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/Requests/Dummy/StoreOrUpdate.php.tpl');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("return []", 'return [' . PHP_EOL . '        ' . implode(',' . PHP_EOL . '        ', $validation_rules) . '' . PHP_EOL . '    ]', $file_contents);
            file_put_contents(app_path('Http/Requests/' . $model_name . '/StoreOrUpdate' . '.php'), $file_contents);


            $dir = app_path('Logic/Helpers/Traits');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            //generate bootable trait
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/Traits/DummyBootableTrait.php.tpl');
            file_put_contents(app_path('Logic/Helpers/Traits/BootableTrait.php'), $file_contents);

        }

        // if ($this->confirm('Create tests for generated CRUD?')) {
        if(!$this->option('no-t')) {
            Artisan::call('generate:test',
                [
                    'table' => $table
                ]
            );
            if (!$this->option('no-tr')) {
                Artisan::call('generate:test-response',
                    [
                        'path/test_name' => $model_name . '/' . $model_name . 'Test'
                    ]
                );
            }
        }
        $this->info("Tests created!");


        // }

        $this->info("Code generated succesfully!");
        //generate policy
        //put policy to policies list


        //GENERATE BACK-END CODE END

    }

    private function convertDatabaseColumnTypeToValidationRule($column_type)
    {
        if (strstr($column_type, 'tinyint(1)') != false)
            return "boolean";
        if (strstr($column_type, 'int') != false)
            return "integer";
        if (strstr($column_type, 'decimal') != false)
            return "numeric";
//        if(strstr($column_type,'varchar')!=false)
//            return "alpha";
//        if(strstr($column_type,'text')!=false)
//            return "alpha";
        if (strstr($column_type, 'date') != false)
            return "date";
        if (strstr($column_type, 'timestamp') != false)
            return "date";
        return "";
    }

    private function convertDatabaseColumnTypeToAngularType($column_type)
    {
        if (strstr($column_type, 'tinyint(1)') != false)
            return "boolean";
        if (strstr($column_type, 'int') != false)
            return "number";
        if (strstr($column_type, 'decimal') != false)
            return "number";
        if (strstr($column_type, 'varchar') != false)
            return "string";
        if (strstr($column_type, 'text') != false)
            return "string";
        if (strstr($column_type, 'date') != false)
            return "date";
        if (strstr($column_type, 'timestamp') != false)
            return "date";
        return "";
    }
}
