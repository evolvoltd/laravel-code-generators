<?php

namespace Evolvo\LaravelCodeGenerators;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoGenerateTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:test {test_name?} {api_resource_route?} {table?}';

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
        $testName = $this->argument('test_name');
        $route = $this->argument('api_resource_route');
        $table = $this->argument('table');
        $fillable_fields = '';

        if(!$testName && !$route && !$table) {
            $testName = $this->ask('Enter test name (E.g. Clients)');
            $route = $this->ask('Enter apiResource route name (E.g. clients)');
            $table = $this->ask('Enter table name if you wish to generate table fields (E.g. clients)');
        }

        if ($table) {
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

                    $collumn_values[] = $this->getStoreData($value->Type);


                }
            }


            foreach (array_combine($fillable_columns, $collumn_values) as $field => $value) {
                $pieces[] = '"' . $field . '"' . ' => ' . $value . ', ';
            }
            // $fillable_fields = serialize($fillable);

            $fillable_fields = implode("\n", $pieces);
        }

        //generate test
        $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummyTest.php.tpl');
        $file_contents = str_replace("testClass",$testName . 'Test', $file_contents);
        $file_contents = str_replace("/*table*/", $table, $file_contents);

        $file_contents = str_replace("testStore()", "test" . $testName . "Store()", $file_contents);
        $file_contents = str_replace("testUpdate()", "test" . $testName . "Update()", $file_contents);
        $file_contents = str_replace("testIndex()", "test" . $testName . "Index()", $file_contents);
        $file_contents = str_replace("testShow()", "test" . $testName . "Show()", $file_contents);
        $file_contents = str_replace("testDelete()", "test" . $testName . "Delete()", $file_contents);

        $file_contents = str_replace("api/store", "api/" . $route, $file_contents);
        $file_contents = str_replace("api/update", "api/" . $route . "/1", $file_contents);
        $file_contents = str_replace("api/index", "api/" . $route, $file_contents);
        $file_contents = str_replace("api/show", "api/" . $route . "/1", $file_contents);
        $file_contents = str_replace("api/delete", "api/" . $route . "/1", $file_contents);


        $file_contents = str_replace("post_data", $fillable_fields, $file_contents);
        $file_contents = str_replace("update_data", $fillable_fields, $file_contents);

        !file_exists(base_path('tests/Feature/' . $testName))?mkdir(base_path('tests/Feature/' . $testName)):null;

        $sufix = $testName . 'Test' . '.php';
        
        file_put_contents(base_path('tests/Feature/' . $testName . '/' . $sufix), $file_contents);

        $prepair_test_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummyDataBasePrepairTest.tpl');
        if(!file_exists(base_path('tests/Feature/DataBasePrepairTest.php'))) {
            file_put_contents(base_path('tests/Feature/DataBasePrepairTest.php'), $prepair_test_contents);
        }
        $phpUnitFile = file_get_contents(base_path('phpunit.xml'));
        
        if (strpos($phpUnitFile, '<env name="DB_DATABASE" value=') == false) {
            $phpUnitFile = str_replace("<php>", "<php>
        <env name=\"DB_DATABASE\" value=\"homestead\"/>", $phpUnitFile);
            
        }

        if (strpos($phpUnitFile, '<directory suffix="DataBasePrepairTest.php">./tests/Feature</directory>') == false) {
            $phpUnitFile = str_replace("<testsuite name=\"Feature\">", "<testsuite name=\"Feature\">
            <directory suffix=\"DataBasePrepairTest.php\">./tests/Feature</directory>", $phpUnitFile);
        }

        $phpUnitFile = str_replace("<directory suffix=\"Test.php\">./tests/Feature</directory>", 
          "<directory suffix=\"".$sufix."\">./tests/Feature/".$testName."</directory>
            <directory suffix=\"Test.php\">./tests/Feature</directory>", $phpUnitFile);

        file_put_contents(base_path('phpunit.xml'), $phpUnitFile);
        
        
        $this->info($testName . "test template created!");

        
    }


    
    
    private function getStoreData($column_type)
    {
        if (strstr($column_type, 'tinyint(1)') != false)
            return 1;
        if (strstr($column_type, 'int') != false)
            return 1;
        if (strstr($column_type, 'decimal') != false)
            return 1;
        if (strstr($column_type, 'varchar') != false)
            return "'alpha'";
        if (strstr($column_type, 'text') != false)
            return "'alpha'";
        if (strstr($column_type, 'date') != false)
            return "'" . Carbon::now() . "'";
        if (strstr($column_type, 'timestamp') != false)
            return "'" . Carbon::now() . "'";
        return "";
    }


}
