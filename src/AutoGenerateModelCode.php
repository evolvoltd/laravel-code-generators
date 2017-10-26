<?php

namespace Evolvo\LaravelCodeGenerators;

use App\Providers\AuthServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AutoGenerateModelCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'auto-generate-code {--table=} {--all}';

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
        $table_names = [];

        if($this->option('all')) {

            $tables = DB::select('SHOW TABLES');
            foreach ($tables as $table) {
                $table_names[] = $table->{'Tables_in_' . env('DB_DATABASE')};
            }
            $table_names = array_keys(array_except(array_flip($table_names), ['migrations', 'password_resets',]));
        }
        else{
            if($this->option('table')!=null) {
                $table = $this->option('table');
                //check if table exists
                if (!DB::getSchemaBuilder()->hasTable($table)) {
                    $this->error(PHP_EOL . 'Table doesn`t exist.' . PHP_EOL);
                    exit;
                }
                $table_names[] = $table;
            }
        }

        if(count($table_names)==0){
            $this->error(PHP_EOL . 'Please specify table name --table=my_table or add --all flag for all tables.' . PHP_EOL);
            exit;
        }

        //dd($table_names);


        foreach($table_names as $table){
        $columns = DB::select('show columns from ' . $table);
        $fillable_columns = [];
        $boolean_columns = [];
        $validation_rules = [];
        foreach ($columns as $value) {
            if(!in_array($value->Field,['id','created_at','updated_at','created_by','updated_by'])){
                $fillable_columns[] = $value->Field;

                $validation_rules[] = '"' . $value->Field . '" => "required|' . $this->convertDatabaseColumnTypeToValidationRule($value->Type) . '"';
            }

            if($value->Type=='tinyint(1)')
                $boolean_columns[] = $value->Field;


        }
        if(substr($table, -1)=='s')
        $singular_table_name = substr($table, 0, -1);
        $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));

        //GENERATE BACK-END CODE START


        //generate model
        $file_contents = file_get_contents(__DIR__ . '/Templates/Model.php');
        $file_contents = str_replace("Dummy",$model_name,$file_contents);
        $file_contents = str_replace("dummy",$table,$file_contents);
        $file_contents = str_replace("fillable = []",'fillable = ['.PHP_EOL.'        "'.implode('",'.PHP_EOL.'        "',$fillable_columns).'"'.PHP_EOL.'    ]',$file_contents);
        $file_contents = str_replace("casts = []",'casts = ['.PHP_EOL.'        "'.implode('" => "boolean",'.PHP_EOL.'        "',$boolean_columns).'" => "boolean"'.PHP_EOL.'    ]',$file_contents);
        $file_contents = str_replace("rules = []",'rules = ['.PHP_EOL.'        '.implode(','.PHP_EOL.'        ',$validation_rules).''.PHP_EOL.'    ]',$file_contents);

        $add_block_comment = File::exists(app_path(''.$model_name.'.php'));
        file_put_contents(app_path(''.$model_name.'.php'),($add_block_comment?'/*':'').$file_contents.($add_block_comment?'*/':''), FILE_APPEND);


        //generate controller
        $file_contents = file_get_contents(__DIR__  .'/Templates/Controller.php');
        $file_contents = str_replace("DummyController",$model_name.'sController',$file_contents);
        $file_contents = str_replace("Dummy",$model_name,$file_contents);
        $add_block_comment = File::exists(app_path('Http/Controllers/'.$model_name.'sController.php'));
        file_put_contents(app_path('Http/Controllers/'.$model_name.'sController.php'),($add_block_comment?'/*':'').$file_contents.($add_block_comment?'*/':''), FILE_APPEND);


        //generate policies
        Artisan::call("make:policy", ["name"=> $model_name."Policy", "--model"=>$model_name]);

        //add policies to AuthServiceProvider
        $file_contents = file_get_contents(app_path('Providers/AuthServiceProvider.php'));
        $file_contents = str_replace("protected \$policies = [",
            "protected \$policies = [".PHP_EOL.
            "        'App\\".$model_name."' => 'App\\Policies\\".$model_name."Policy',"
            ,$file_contents);
        file_put_contents(app_path('Providers/AuthServiceProvider.php'), $file_contents);

        //edit policy methods
        //

         //genereate routes
         $file_contents = PHP_EOL."    Route::apiResource('".str_replace('_','-', strtolower($table))."', '".$model_name."sController');";
         file_put_contents(base_path('routes/api.php'),$file_contents, FILE_APPEND);

        //GENERATE BACK-END CODE END

        //GENERATE FRONT-END CODE START

        //generate list view file
        //generate list view controller
        //generate create new view
        //generate create new controller
        //generate show item view
        //generate show item controller


        //GENERATE FRONT-END CODE END

        //check if any file exists
        //confirm overwrite or overwrite all
        }
        exec('composer dump-autoload', $result, $return_var);
        $this->comment(PHP_EOL.$return_var.PHP_EOL);
        print_r($result);
    }
    private function convertDatabaseColumnTypeToValidationRule($column_type){
        if(strstr($column_type,'tinyint(1)')!=false)
            return "boolean";
        if(strstr($column_type,'int')!=false)
            return "integer";
        if(strstr($column_type,'decimal')!=false)
            return "numeric";
        if(strstr($column_type,'varchar')!=false)
            return "alpha";
        if(strstr($column_type,'text')!=false)
            return "alpha";
        if(strstr($column_type,'date')!=false)
            return "date";
        if(strstr($column_type,'timestamp')!=false)
            return "date";
        return "";
    }
}
