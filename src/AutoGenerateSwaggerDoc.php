<?php

namespace Evolvo\LaravelCodeGenerators;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoGenerateSwaggerDoc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:swagger {table} {route?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Makes swagger doc';

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

        $singular_table_name = (substr($table, strlen($table)-4, 3)=='ies')?(substr($table, 0, -3).'y'):(substr($table, 0, -1));
        if(substr($table, -1)=='s')
            $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));
        else
            $model_name = str_replace('_', '', ucwords($table, '_'));


        $swaggerName = $model_name;//gument('test_name');
        $route = str_replace('_', '-', $table);


        $fillable_fields = '';

//        if(!$table) {
//            $testName = $this->ask('Enter model name (E.g. Client)');
//            $route = $this->ask('Enter apiResource route name (E.g. '.lcfirst($testName).'s)');
//            $table = $this->ask('Enter table name if you wish to generate table fields (E.g. '.lcfirst($testName).'s)');
//        }

        if ($table) {
            try {
                $columns = DB::select('show columns from ' . $table);
            } catch (\Exception $e) {
                $this->info("Table not found, enter correct table name!");
                return true;
            }

            $fillable_columns = [];
            $boolean_columns = [];
            $validation_rules = [];
            $table_headers = [];
            $table_columns = [];
            $angular_model_attributes = [];
            $form_fields = [];
            $singular_table_name = (substr($table, strlen($table) - 4, 3) == 'ies') ? (substr($table, 0, -3) . 'y') : (substr($table, 0, -1));
            $parameters = '';
            foreach ($columns as $value) {
                if (!in_array($value->Field, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) {
//                    $fillable_columns[] = $value->Field;
//
//                    $collumn_values[] = $this->getStoreData($value->Type);


                    $parameters .= '
 * 
 *          @OA\Parameter(
 *          name="' . $value->Field . '",
 *          description="' . $value->Field . '",
 *          required=true,
 *          in="query",
 *          @OA\Schema(
 *              type="' . $value->Type . '"
 *          )
 *      ),
 ';


                }
            }


            //generate swagger documentation
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummySwagger.php.tpl');
            $file_contents = str_replace("[#'uri'#]", $route, $file_contents);
            $file_contents = str_replace("[#'post_parameters'#]", $parameters, $file_contents);
            $file_contents = str_replace("[#'put_parameters'#]", $parameters, $file_contents);
            $file_contents = str_replace("[#'id'#]", lcfirst($route), $file_contents);

            !file_exists(base_path('tests/api-docs/'))?mkdir(base_path('tests/api-docs/')):null;
            
            file_put_contents(base_path('tests/api-docs/' . ucfirst($route) . '.php'), $file_contents);


            $this->info($swaggerName . " swagger doc created!");

        }
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
