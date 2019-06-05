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
    protected $signature = 'scaffold {database_table} {--only-ng} {--only-vue}';

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
        if(!DB::getSchemaBuilder()->hasTable($table)) {
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

        $vue_form_fields = [];
        $vue_table_columns = [];
        $vue_table_row_details = [];
        $vue_first_form_field = '';

        $form_fields = [];
        $column_index = 0;
        $singular_table_name = (substr($table, strlen($table)-4, 3)=='ies')?(substr($table, 0, -3).'y'):(substr($table, 0, -1));
        foreach ($columns as $value) {
            if(!in_array($value->Field,['id','created_at','updated_at','created_by','updated_by'])){
                $fillable_columns[] = $value->Field;

                $validation_rules[] = '"' . $value->Field . '" => "required|' . $this->convertDatabaseColumnTypeToValidationRule($value->Type) . '"';
                $angular_model_attributes[] = $value->Field . ': '. $this->convertDatabaseColumnTypeToAngularType($value->Type).';';
                $table_headers[] = '<th>'.ucfirst($value->Field).'</th>';
                $table_columns[] = '<td>{{'.$singular_table_name.'.'.$value->Field.'}}</td>';
                $vue_form_fields[] =
                    '<v-flex xs12 sm6>'.PHP_EOL.
                        '<v-text-field'.PHP_EOL.
                            'v-model="'.$singular_table_name.'.'.$value->Field.'"'.PHP_EOL.
                            ':error-messages="errors[\''.$value->Field.'\']"'.PHP_EOL.
                            ':rules="[]"'.PHP_EOL.
                            ':label="$t(\''.$value->Field.'\')"'.PHP_EOL.
                            'name="'.$value->Field.'"'.PHP_EOL.
                            '@blur="formMixin_clearErrors(\''.$value->Field.'\')"'.PHP_EOL.
                        '/>'.PHP_EOL.
                    '</v-flex>'.PHP_EOL;

                if ($column_index > 0) {
                    $vue_table_columns[] =
                        '<td v-if="!$vuetify.breakpoint[headers['.$column_index.'].hidden]">'.PHP_EOL.
                            '{{ props.item.'.$value->Field.' }}'.PHP_EOL.
                        '</td>'.PHP_EOL;
                } else {
                    $vue_first_form_field = $value->Field;
                    $vue_table_columns[] =
                        '<td>'.PHP_EOL.
                        '{{ props.item.'.$value->Field.' }}'.PHP_EOL.
                        '</td>'.PHP_EOL;
                }

                $vue_table_row_details[] =
                    '<v-layout'.PHP_EOL.
                      'v-if="headers['.$column_index.'].hidden"'.PHP_EOL.
                      'class="row-detail-item"'.PHP_EOL.
                      'justify-space-between'.PHP_EOL.
                      'align-center>'.PHP_EOL.
                      '<strong>'.PHP_EOL.
                        '{{ headers['.$column_index.'].text }}:'.PHP_EOL.
                      '</strong>'.PHP_EOL.
                      '<span class="text-xs-right">'.PHP_EOL.
                        '{{ props.item.'.$value->Field.' }}'.PHP_EOL.
                      '</span>'.PHP_EOL.
                    '</v-layout>'.PHP_EOL;

                $form_fields[] =
                    '<div class="form-group">'.PHP_EOL.
                    '<label>'.ucfirst($value->Field).'</label>'.PHP_EOL.
                    '<input type="text" class="form-control" [(ngModel)]="'.$singular_table_name.'.'.$value->Field.'" placeholder="'.ucfirst($value->Field).'">'.PHP_EOL.
                    '</div>';

                $column_index++;
            }

            if($value->Type=='tinyint(1)')
                $boolean_columns[] = $value->Field;
        }
        if(substr($table, -1)=='s')
            $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));
        else
            $model_name = str_replace('_', '', ucwords($table, '_'));

        if($this->option('only-ng')){
            $dir = app_path('Console/Commands/Output/Angular/'.$table.'/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-paginated.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$singular_table_name.'-paginated.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummies.component.html');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            $file_contents = str_replace("DUMMY_HEADERS",implode(PHP_EOL, $table_headers),$file_contents);
            $file_contents = str_replace("DUMMY_COLUMNS",implode(PHP_EOL, $table_columns),$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$table.'.component.html'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummies.component.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$table.'.component.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy.service.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$table.'.service.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("MODEL_ATTRIBUTES",implode(PHP_EOL, $angular_model_attributes),$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$singular_table_name.'.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-detail.component.html');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$singular_table_name.'-detail.component.html'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-detail.component.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$singular_table_name.'-detail.component.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-modal.component.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$singular_table_name.'-modal.component.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-modal.component.html');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            $file_contents = str_replace("FORM_FIELDS",implode(PHP_EOL, $form_fields),$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/'.$singular_table_name.'-modal.component.html'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/app.module.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/app.module.ts'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/app-routing.module.ts');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/'.$table.'/app-routing.module.ts'),$file_contents);

        } else if ($this->option('only-vue')) {
            $dir = app_path('Console/Commands/Output/Vue/'.$table.'/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyForm.vue');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            $file_contents = str_replace("VUE_FORM_FIELDS",implode(PHP_EOL, $vue_form_fields),$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table.'/'.ucfirst($singular_table_name).'Form.vue'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyForm.spec.js');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            $file_contents = str_replace("VUE_FORM_FIELD_NAME",$vue_first_form_field,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table.'/'.ucfirst($singular_table_name).'Form.spec.js'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyTable.vue');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            $file_contents = str_replace("VUE_TABLE_COLUMNS",implode(PHP_EOL, $vue_table_columns),$file_contents);
            $file_contents = str_replace("VUE_TABLE_ROW_DETAILS",implode(PHP_EOL, $vue_table_row_details),$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table.'/'.ucfirst($singular_table_name).'Table.vue'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyTable.spec.js');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table.'/'.ucfirst($singular_table_name).'Table.spec.js'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/Dummys.vue');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$singular_table_name,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table.'/'.ucfirst($singular_table_name).'s.vue'),$file_contents);

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
            //fill custom translation rules

            //generate controller
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/DummyController.php.tpl');
            $file_contents = str_replace("DummyController", $model_name . 'sController', $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            file_put_contents(app_path('Http/Controllers/' . $model_name . 'sController' . '.php'), $file_contents);


            $dir = app_path('Http/Requests/' . $model_name . '/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            //generate request class
            $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/Requests/Dummy/StoreOrUpdate.php.tpl');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("return []", 'return [' . PHP_EOL . '        ' . implode(',' . PHP_EOL . '        ', $validation_rules) . '' . PHP_EOL . '    ]', $file_contents);
            file_put_contents(app_path('Http/Requests/' . $model_name . '/StoreOrUpdate' . '.php'), $file_contents);
        }



        if ($this->confirm('Create tests for generated CRUD?')) {
            Artisan::call('generate:test',
                [
                    'test_name'=>$model_name,
                    'api_resource_route'=>$table,
                    'table'=>$table
                ]
            );
            $this->info("Tests created!");
        }

        $this->info("Code generated succesfully!");
        //generate policy
        //put policy to policies list

        //genereate routes

        //GENERATE BACK-END CODE END

    }
    private function convertDatabaseColumnTypeToValidationRule($column_type){
        if(strstr($column_type,'tinyint(1)')!=false)
            return "boolean";
        if(strstr($column_type,'int')!=false)
            return "integer";
        if(strstr($column_type,'decimal')!=false)
            return "numeric";
//        if(strstr($column_type,'varchar')!=false)
//            return "alpha";
//        if(strstr($column_type,'text')!=false)
//            return "alpha";
        if(strstr($column_type,'date')!=false)
            return "date";
        if(strstr($column_type,'timestamp')!=false)
            return "date";
        return "";
    }

    private function convertDatabaseColumnTypeToAngularType($column_type){
        if(strstr($column_type,'tinyint(1)')!=false)
            return "boolean";
        if(strstr($column_type,'int')!=false)
            return "number";
        if(strstr($column_type,'decimal')!=false)
            return "number";
        if(strstr($column_type,'varchar')!=false)
            return "string";
        if(strstr($column_type,'text')!=false)
            return "string";
        if(strstr($column_type,'date')!=false)
            return "date";
        if(strstr($column_type,'timestamp')!=false)
            return "date";
        return "";
    }
}
