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
        $vue_table_headers = 'const headers = ['.PHP_EOL;
        $vue_table_columns = [];
        $vue_table_row_details = [];
        $vue_first_form_field = '';
        $vue_form_data_attributes = '';
        $vue_form_imports = '';
        $vue_form_components = '';
        $is_vue_autocomplete_imported = false;
        $vue_translations = '';

        $form_fields = [];
        $column_index = 0;
        $singular_table_name = (substr($table, strlen($table)-4, 3)=='ies')?(substr($table, 0, -3).'y'):(substr($table, 0, -1));

        if(substr($table, -1)=='s')
            $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));
        else
            $model_name = str_replace('_', '', ucwords($table, '_'));

        foreach ($columns as $value) {
            if(!in_array($value->Field,['id','created_at','updated_at','deleted_at','created_by','updated_by','deleted_by'])) {
                $fillable_columns[] = $value->Field;

                $validation_rules[] = '"' . $value->Field . '" => "required|' . $this->convertDatabaseColumnTypeToValidationRule($value->Type) . '"';
                $angular_model_attributes[] = $value->Field . ': '. $this->convertDatabaseColumnTypeToAngularType($value->Type).';';
                $table_headers[] = '<th>'.ucfirst($value->Field).'</th>';
                $table_columns[] = '<td>{{'.$singular_table_name.'.'.$value->Field.'}}</td>';

                if (strpos($value->Field, '_id') !== false) {
                    if (!$is_vue_autocomplete_imported) {
                        $vue_form_imports = $vue_form_imports.
                            'import Autocomplete from \'./Autocomplete\''.PHP_EOL;
                        $vue_form_components = $vue_form_components.
                            'Autocomplete,'.PHP_EOL;
                    }

                    $object_field = str_replace('_id', '', $value->Field);
                    $object_field = $this->toCamelCase($object_field);
                    $vue_form_imports = $vue_form_imports.
                        'import { '.$object_field.'Service } from \'../services/'.$object_field.'-service\';'.PHP_EOL;
                    $vue_form_data_attributes = $vue_form_data_attributes.
                        $object_field.'SearchFunction: '.$object_field.'Service.search,'.PHP_EOL;

                    $vue_form_fields[] = $this->getVueAutocompleteField($value->Field, $object_field, $singular_table_name);
                    $is_vue_autocomplete_imported = true;
                    $vue_translations = $vue_translations.'"'.$object_field.'": "",'.PHP_EOL;
                } else if ($value->Type === 'date') {
                    $date_picker_attribute = 'is' . $this->toPascalCase($value->Field) . 'PickerOpen';
                    $vue_form_data_attributes = $vue_form_data_attributes.$date_picker_attribute.': false,'.PHP_EOL;
                    $vue_form_fields[] = $this->getVueDateField($value->Field, $date_picker_attribute, $singular_table_name);
                    $vue_translations = $vue_translations.'"'.$value->Field.'": "",'.PHP_EOL;
                } else if ($value->Type === 'tinyint(1)') {
                    $vue_form_fields[] = $this->getVueCheckboxField($value->Field, $singular_table_name, $value->Null);
                    $vue_translations = $vue_translations.'"'.$value->Field.'": "",'.PHP_EOL;
                } else {
                    $vue_form_fields[] = $this->getVueTextField($value->Field, $singular_table_name, $value->Null);
                    $vue_translations = $vue_translations.'"'.$value->Field.'": "",'.PHP_EOL;
                }

                if ($column_index === 0) {
                    $vue_first_form_field = $value->Field;
                    $vue_table_headers = $vue_table_headers.'{ text: this.$t(\''.$value->Field.'\') },'.PHP_EOL;
                } else {
                    $vue_table_headers = $vue_table_headers.'{ text: this.$t(\''.$value->Field.'\'), hidden: \'xsOnly\' },'.PHP_EOL;
                }
                $vue_table_columns[] = $this->getVueTableColumn($value->Field, $column_index, $value->Type);
                $vue_table_row_details[] = $this->getVueRowDetail($value->Field, $column_index, $value->Type);

                $form_fields[] = $this->getAngularFormField($value->Field, $singular_table_name);

                $column_index++;
            }

            if($value->Type=='tinyint(1)') {
                $boolean_columns[] = $value->Field;
            }
        }

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
            $table_in_kebab_case = $this->toKebabCase($table);

            $dir = app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $model_in_camel_case = $this->toCamelCase($singular_table_name);
            $model_in_kebab_case = $this->toKebabCase($singular_table_name);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyForm.vue');
            $file_contents = str_replace("dummysc",$singular_table_name,$file_contents);
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$model_in_camel_case,$file_contents);
            $file_contents = str_replace("VUE_FORM_FIELDS",implode(PHP_EOL, $vue_form_fields),$file_contents);
            $file_contents = str_replace("VUE_FORM_DATA_ATTRIBUTES",$vue_form_data_attributes,$file_contents);
            $file_contents = str_replace("VUE_FORM_IMPORTS",$vue_form_imports,$file_contents);
            $file_contents = str_replace("VUE_FORM_COMPONENTS",$vue_form_components,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/'.$model_name.'Form.vue'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyForm.spec.js');
            $file_contents = str_replace("dummykc",$model_name,$model_in_kebab_case);
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$model_in_camel_case,$file_contents);
            $file_contents = str_replace("VUE_FORM_FIELD_NAME",$vue_first_form_field,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/'.$model_name.'Form.spec.js'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyTable.vue');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$model_in_camel_case,$file_contents);
            $file_contents = str_replace("VUE_TABLE_HEADERS",$vue_table_headers,$file_contents);
            $file_contents = str_replace("VUE_TABLE_COLUMNS",implode(PHP_EOL, $vue_table_columns),$file_contents);
            $file_contents = str_replace("VUE_TABLE_ROW_DETAILS",implode(PHP_EOL, $vue_table_row_details),$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/'.$model_name.'Table.vue'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyTable.spec.js');
            $file_contents = str_replace("dummykc",$model_name,$model_in_kebab_case);
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$model_in_camel_case,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/'.$model_name.'Table.spec.js'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/Dummys.vue');
            $file_contents = str_replace("dummykc",$model_name,$model_in_kebab_case);
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$model_in_camel_case,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/'.$model_name.'s.vue'),$file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/dummy-service.js');
            $file_contents = str_replace("Dummy",$model_name,$file_contents);
            $file_contents = str_replace("dummy",$model_in_camel_case,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/'.$model_in_kebab_case.'-service.js'),$file_contents);

            $vue_translations = $vue_translations.
                '"new_'.$singular_table_name.'": "",'.PHP_EOL.
                '"edit_'.$singular_table_name.'": ""';
            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/translations.json');
            $file_contents = str_replace("VUE_TRANSLATIONS",$vue_translations,$file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/'.$table_in_kebab_case.'/translations.json'),$file_contents);

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

    private function getVueTextField(string $field, string $singular_table_name, string $is_null): string {
        $form_item_name = $this->toCamelCase($singular_table_name);

        $result =
            '<v-flex xs12 sm6>'.PHP_EOL.
                '<v-text-field'.PHP_EOL.
                    'v-model="'.$form_item_name.'.'.$field.'"'.PHP_EOL.
                    ':error-messages="errors[\''.$field.'\']"'.PHP_EOL;

        if ($is_null === 'NO') {
            $result = $result.':rules="[required]"'.PHP_EOL;
        } else {
            $result = $result.':rules="[]"'.PHP_EOL;
        }

        $result = $result.
                    ':label="$t(\''.$field.'\')"'.PHP_EOL.
                    'name="'.$field.'"'.PHP_EOL.
                    '@blur="formMixin_clearErrors(\''.$field.'\')"'.PHP_EOL.
                '/>'.PHP_EOL.
            '</v-flex>'.PHP_EOL;

        return $result;
    }

    private function getVueAutocompleteField(string $id_field, string $object_field, string $singular_table_name): string {
        $form_item_name = $this->toCamelCase($singular_table_name);
        $result =
            '<v-flex xs12 sm6>'.PHP_EOL.
                '<Autocomplete'.PHP_EOL.
                    ':search-function="'.$object_field.'SearchFunction"'.PHP_EOL.
                    ':item="'.$form_item_name.'.'.$object_field.'"'.PHP_EOL.
                    ':error-messages="errors.'.$id_field.'"'.PHP_EOL.
                    ':label="$t(\''.$object_field.'\')"'.PHP_EOL.
                    'text-field="id"'.PHP_EOL.
                    'hint="Currently displays #id in the options list, change form field\'s text-field value to change it"'.PHP_EOL.
                    '@itemSelected="formMixin_setAutocompleteValue($event, \''.$object_field.'\')"'.PHP_EOL.
                '/>'.PHP_EOL.
            '</v-flex>'.PHP_EOL;

        return $result;
    }

    private function getVueCheckboxField(string $field, string $singular_table_name, string $is_null): string {
        $form_item_name = $this->toCamelCase($singular_table_name);

        $result =
            '<v-flex xs12 sm6>'.PHP_EOL.
                '<v-checkbox'.PHP_EOL.
                    'v-model="'.$form_item_name.'.'.$field.'"'.PHP_EOL.
                    ':error-messages="errors[\''.$field.'\']"'.PHP_EOL;

        if ($is_null === 'NO') {
            $result = $result.':rules="[required]"'.PHP_EOL;
        } else {
            $result = $result.':rules="[]"'.PHP_EOL;
        }

        $result = $result.
                    ':label="$t(\''.$field.'\')"'.PHP_EOL.
                    'name="'.$field.'"'.PHP_EOL.
                    '@blur="formMixin_clearErrors(\''.$field.'\')"'.PHP_EOL.
                '/>'.PHP_EOL.
            '</v-flex>'.PHP_EOL;

        return $result;
    }

    private function getVueDateField(string $field, string $date_picker_attribute, string $singular_table_name): string {
        $form_item_name = $this->toCamelCase($singular_table_name);

        return
            '<v-flex xs12 sm6>'.PHP_EOL.
                '<v-menu'.PHP_EOL.
                    'v-model="'.$date_picker_attribute.'"'.PHP_EOL.
                    ':close-on-content-click="false"'.PHP_EOL.
                    'min-width="290px"'.PHP_EOL.
                    'lazy'.PHP_EOL.
                    'offset-y'.PHP_EOL.
                    'full-width>'.PHP_EOL.
                    '<v-text-field'.PHP_EOL.
                        'slot="activator"'.PHP_EOL.
                        ':value="'.$form_item_name.'.'.$field.'"'.PHP_EOL.
                        ':label="$t(\''.$field.'\')"'.PHP_EOL.
                        'append-icon="event"'.PHP_EOL.
                        '@blur="'.$form_item_name.'.'.$field.' = $formatDate($event.target.value)"'.PHP_EOL.
                    '/>'.PHP_EOL.
                    '<v-date-picker'.PHP_EOL.
                        'v-model="'.$form_item_name.'.'.$field.'"'.PHP_EOL.
                        ':locale="$store.state.settings.locale"'.PHP_EOL.
                        'first-day-of-week="1"'.PHP_EOL.
                        'no-title'.PHP_EOL.
                        'scrollable'.PHP_EOL.
                        '@input="'.$date_picker_attribute.' = false"'.PHP_EOL.
                    '/>'.PHP_EOL.
                '</v-menu>'.PHP_EOL.
            '</v-flex>'.PHP_EOL;
    }

    private function getVueRowDetail(string $field, int $column_index, string $column_type): string {
        if ($column_index === 0) {
            return '';
        }

        $result =
            '<v-layout'.PHP_EOL.
                'v-if="headers['.$column_index.'].hidden"'.PHP_EOL.
                'class="row-detail-item"'.PHP_EOL.
                'justify-space-between'.PHP_EOL.
                'align-center>'.PHP_EOL.
                '<strong>'.PHP_EOL.
                    '{{ headers['.$column_index.'].text }}:'.PHP_EOL.
                '</strong>'.PHP_EOL.
                '<span class="text-xs-right">'.PHP_EOL;

        if ($column_type === 'tinyint(1)') {
            $result = $result.
                '<v-icon>'.PHP_EOL.
                    '{{ props.item.'.$field.' ? \'check_box\' : \'check_box_outline_blank\' }}'.PHP_EOL.
                '</v-icon>'.PHP_EOL;
        } else {
            $result = $result.'{{ props.item.'.$field.' }}'.PHP_EOL;
        }
        $result = $result.
                '</span>'.PHP_EOL.
            '</v-layout>'.PHP_EOL;

        return $result;
    }

    private function getVueTableColumn(string $field, int $column_index, string $column_type): string {
        if ($column_index > 0) {
            $result = '<td v-if="!$vuetify.breakpoint[headers['.$column_index.'].hidden]">'.PHP_EOL;
        } else {
            $result = '<td>'.PHP_EOL;
        }

        if ($column_type === 'tinyint(1)') {
            $result = $result.
                    '<v-icon>'.PHP_EOL.
                        '{{ props.item.'.$field.' ? \'check_box\' : \'check_box_outline_blank\' }}'.PHP_EOL.
                    '</v-icon>'.PHP_EOL.
                '</td>'.PHP_EOL;
        } else {
            $result = $result.
                    '{{ props.item.'.$field.' }}'.PHP_EOL.
                '</td>'.PHP_EOL;
        }

        return $result;
    }

    private function getAngularFormField(string $field, string $singular_table_name): string {
        return
            '<div class="form-group">'.PHP_EOL.
            '<label>'.ucfirst($field).'</label>'.PHP_EOL.
            '<input type="text" class="form-control" [(ngModel)]="'.$singular_table_name.'.'.$field.'" placeholder="'.ucfirst($field).'">'.PHP_EOL.
            '</div>';
    }

    private function toPascalCase(string $str): string {
        return str_replace('_', '', ucwords($str, '_'));
    }

    private function toCamelCase(string $str): string {
        return str_replace('_', '', lcfirst(ucwords($str, '_')));
    }

    private function toKebabCase(string $str): string {
        return str_replace('_', '-', strtolower($str));
    }
}
