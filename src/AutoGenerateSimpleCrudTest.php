<?php

namespace Evolvo\LaravelCodeGenerators;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AutoGenerateSimpleCrudTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simple-crud-test {table?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes test template';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $table = $this->argument('table');

        $singular_table_name = Str::singular($table);
        $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));
        $model_name_plural = Str::plural($model_name);


        $testName = $model_name_plural;
        $route = str_replace('_', '-', $table);
        $modelClassUsages = [];


        $fillable_fields = '';

        if(!$table) {
            $testName = $this->ask('Enter model name (E.g. Client)');
            $route = $this->ask('Enter apiResource route name (E.g. '.lcfirst($testName).'s)');
            $table = $this->ask('Enter table name if you wish to generate table fields (E.g. '.lcfirst($testName).'s)');
        }

        if ($table) {
            try {
                $columns = DB::select('show columns from ' . $table);
            } catch (\Exception $e) {
                $this->info("Table not found, enter correct table name!");
                return true;
            }

            $fillable_columns = [];
            foreach ($columns as $value) {
                if (!in_array($value->Field, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) {
                    $fillable_columns[] = $value->Field;

                    $collumn_values[] = $this->convertDatabaseColumnTypeToFakerFunction($value->Type, $value->Field, $modelClassUsages);


                }
            }

            foreach (array_combine($fillable_columns, $collumn_values) as $field => $value) {
                $pieces[] = '"' . $field . '"' . ' => ' . $value . ', ';
            }
            $fillable_fields = implode(PHP_EOL."            ", $pieces);
        }

        //generate test
        $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummySimpleCrudTest.php.tpl');
        $file_contents = str_replace("//modelClassUsages", implode(PHP_EOL, $modelClassUsages), $file_contents);
        $file_contents = str_replace("testClass",$testName . 'Test', $file_contents);
        $file_contents = str_replace("api/route", "api/" . $route, $file_contents);


        $file_contents = str_replace("post_data", $fillable_fields, $file_contents);
        $file_contents = str_replace("update_data", $fillable_fields, $file_contents);
        $file_contents = str_replace("Dummy", $model_name, $file_contents);
        $file_contents = str_replace("Dummies", $model_name_plural, $file_contents);

        !file_exists(base_path('tests/Feature/' . $testName))?mkdir(base_path('tests/Feature/' . $testName)):null;

        $sufix = $testName . 'Test' . '.php';

        file_put_contents(base_path('tests/Feature/' . $testName . '/' . $sufix), $file_contents);

        $this->info($testName . " test template created!");

    }

    private function convertDatabaseColumnTypeToFakerFunction($column_type, $field_name, &$modelClassUsages)
    {
        if (strstr($column_type, 'tinyint(1)') != false)
            return '$this->faker->boolean';

        if (strstr($column_type, 'int') != false) {
                    if (Str::endsWith($field_name, '_id')) {
                        $attributeModelName = str_replace('_', '', ucwords(substr($field_name, 0, -3), '_'));
                        if($attributeModelName!='User')
                            $modelClassUsages[] = 'use App\\Models\\' . $attributeModelName . ';';
                        return $attributeModelName . '::factory()->create()->id';
                    }
                    else
                        return '$this->faker->numberBetween(0,1000)';
                }
        if (strstr($column_type, 'int') != false)
            return '$this->faker->numberBetween(0,1000)';
        if (strstr($column_type, 'decimal') != false)
            return '$this->faker->randomFloat(2,0.01,999999)';
        if(strstr($column_type,'varchar')!=false)
            return 'Str::random()';
        if(strstr($column_type,'text')!=false)
            return '$this->faker->sentence';
        if (strstr($column_type, 'datetime') != false)
            return '$this->faker->dateTime->format("Y-m-d H:i:s")';
        if (strstr($column_type, 'date') != false)
            return '$this->faker->date()';
        if (strstr($column_type, 'timestamp') != false)
            return '$this->faker->dateTime->format("Y-m-d H:i:s")';
        return "";
    }


}
