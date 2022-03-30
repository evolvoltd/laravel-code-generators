<?php

namespace Evolvo\LaravelCodeGenerators;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        if ($table) {
            try {
                $columns = DB::select('show columns from ' . $table);
            } catch (\Exception $e) {
                $this->info("Table not found, enter correct table name!");
                return true;
            }

            $idParameter =
' *      @OA\Parameter(
 *          name="' . lcfirst($model_name) . '",
 *          description="' . ucfirst(str_replace('_', ' ',$table)) . ' ID",
 *          required=true,
 *          in="path",
 *          @OA\Schema(
 *              type="integer"
 *          )
 *      ),';
            $parameters = [];
            foreach ($columns as $value) {
                if (!in_array($value->Field, ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'])) {

                    $parameters[] =
 ' *      @OA\Parameter(
 *          name="' . $value->Field . '",
 *          description="' . $value->Field . '",
 *          required=true,
 *          in="query",
 *          @OA\Schema(
 *              type="' . $this->getSwaggerParamType($value->Type) . '"
 *          )
 *      ),';
                }
            }
            $parameters = implode(PHP_EOL, $parameters);
            //generate swagger documentation
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummySwagger.php.tpl');
            $file_contents = str_replace("[#'uri'#]", $route, $file_contents);
            $file_contents = str_replace("[#'post_parameters'#]", $parameters, $file_contents);
            $file_contents = str_replace("[#'put_parameters'#]", $parameters, $file_contents);
            $file_contents = str_replace("[#'id'#]", lcfirst($model_name), $file_contents);
            $file_contents = str_replace("[#'tag'#]", ucfirst(str_replace('_', ' ',$table)), $file_contents);
            $file_contents = str_replace("[#'id_parameter'#]", $idParameter, $file_contents);

            !file_exists(base_path('tests/api-docs/'))?mkdir(base_path('tests/api-docs/')):null;

            file_put_contents(base_path('tests/api-docs/' . Str::plural($model_name) . '.php'), $file_contents);

            $this->info($swaggerName . " swagger doc created!");

        }
    }

    private function getSwaggerParamType($column_type)
    {
        if (strstr($column_type, 'tinyint(1)') != false)
            return 'boolean';
        if (strstr($column_type, 'int') != false)
            return 'integer';
        if (strstr($column_type, 'decimal') != false)
            return 'number';
        if (strstr($column_type, 'varchar') != false)
            return "string";
        if (strstr($column_type, 'text') != false)
            return "string";
        if (strstr($column_type, 'date') != false)
            return "string";
        if (strstr($column_type, 'timestamp') != false)
            return "string";
        return "";
    }


}
