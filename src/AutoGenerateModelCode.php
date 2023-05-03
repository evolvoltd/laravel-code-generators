<?php

namespace Evolvo\LaravelCodeGenerators;

use App\Providers\AuthServiceProvider;
use Evolvo\LaravelCodeGenerators\Converters\LaravelConverter;
use Evolvo\LaravelCodeGenerators\Generators\LaravelCodeGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AutoGenerateModelCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scaffold {database_table_name} {--vue} {--angular}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto generate code using only database table structure for Laravel, Vue or Angular';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        //check if table exists
        $databaseTableName = $this->argument('database_table_name');
        if (!DB::getSchemaBuilder()->hasTable($databaseTableName)) {
            $this->comment(PHP_EOL . 'Table doesn`t exist.' . PHP_EOL);
            exit;
        }

        $databaseTableColumns = DB::select('show columns from ' . $databaseTableName);

        $table_headers = [];
        $table_columns = [];
        $angular_model_attributes = [];

        $vue_form_fields = [];
        $vue_table_headers = '';
        $vue_form_data_attributes = '';
        $vue_form_imports = '';
        $vue_form_components = '';
        $is_vue_autocomplete_imported = false;
        $is_vue_datepicker_imported = false;
        $vue_translations = '';

        $form_fields = [];
        $column_index = 0;
        $singular_table_name = Str::singular($databaseTableName);
        $model_name = str_replace('_', '', ucwords($singular_table_name, '_'));

        $model_in_camel_case = $this->toCamelCase($singular_table_name);
        $model_in_kebab_case = $this->toKebabCase($singular_table_name);

        foreach ($databaseTableColumns as $value) {
            if (!in_array($value->Field, ['id', 'created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'])) {

                $angular_model_attributes[] = $value->Field . ': ' . $this->convertDatabaseColumnTypeToAngularType($value->Type) . ';';
                $table_headers[] = '<th>' . ucfirst($value->Field) . '</th>';
                $table_columns[] = '<td>{{' . $singular_table_name . '.' . $value->Field . '}}</td>';
                $vue_field_label = $value->Field;

                if (strpos($value->Field, '_id') !== false) {
                    if (!$is_vue_autocomplete_imported) {
                        $vue_form_imports = $vue_form_imports . PHP_EOL .
                            'import BaseAutocomplete from \'@/components/base/BaseAutocomplete\';';
                        $vue_form_components = $vue_form_components .
                            'BaseAutocomplete,' . PHP_EOL;
                    }

                    $object_field = str_replace('_id', '', $value->Field);
                    $vue_field_label = str_replace('_id', '', $value->Field);
                    $object_in_kebab_case = $this->toKebabCase($object_field);
                    $object_field = $this->toCamelCase($object_field);
                    $vue_form_imports = $vue_form_imports . PHP_EOL .
                        'import ' . $object_field . 'Service from \'@/api/' . $object_in_kebab_case . '-service\';';
                    $vue_form_data_attributes = $vue_form_data_attributes .
                        $object_field . 'SearchFunction: ' . $object_field . 'Service.search,' . PHP_EOL;

                    $vue_form_fields[] = $this->getVueAutocompleteField($value->Field, $object_field);
                    $is_vue_autocomplete_imported = true;
                } else if ($value->Type === 'date') {
                    if (!$is_vue_datepicker_imported) {
                        $vue_form_imports = $vue_form_imports . PHP_EOL .
                            'import BaseDatepickerInput from \'@/components/base/BaseDatepickerInput\';';
                        $vue_form_components = $vue_form_components .
                            'BaseDatepickerInput,' . PHP_EOL;
                    }
                    $vue_form_fields[] = $this->getVueDateField($value->Field);
                    $is_vue_datepicker_imported = true;
                } else if ($value->Type === 'tinyint(1)') {
                    $vue_form_fields[] = $this->getVueCheckboxField($value->Field);
                } else if ($value->Type === 'text') {
                    $vue_form_fields[] = $this->getVueTextArea($value->Field);
                } else if ((strstr($value->Type, 'int') || strstr($value->Type, 'decimal') || strstr($value->Type, 'float'))) {
                    $vue_form_fields[] = $this->getVueNumberField($value->Field);
                } else {
                    $vue_form_fields[] = $this->getVueTextField($value->Field);
                }

                if ($column_index === 0) {
                    $vue_table_headers = $vue_table_headers . '{' . PHP_EOL . 'text: this.$t(\'' . $vue_field_label . '\'),' . PHP_EOL . 'value: \'' . $value->Field . '\',' . PHP_EOL . '},' . PHP_EOL;
                } else {
                    $vue_table_headers = $vue_table_headers . '{' . PHP_EOL . 'text: this.$t(\'' . $vue_field_label . '\'),' . PHP_EOL . 'value: \'' . $value->Field . '\',' . PHP_EOL . 'hidden: \'xsOnly\',' . PHP_EOL . '},' . PHP_EOL;
                }
                $vue_translations = $vue_translations . '"' . $vue_field_label . '": "",' . PHP_EOL;

                $form_fields[] = $this->getAngularFormField($value->Field, $singular_table_name);

                $column_index++;
            }
        }

        if ($this->option('angular')) {
            $dir = app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/');
            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }
            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-paginated.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $singular_table_name . '-paginated.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummies.component.html');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            $file_contents = str_replace("DUMMY_HEADERS", implode(PHP_EOL, $table_headers), $file_contents);
            $file_contents = str_replace("DUMMY_COLUMNS", implode(PHP_EOL, $table_columns), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $databaseTableName . '.component.html'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummies.component.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $databaseTableName . '.component.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy.service.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $databaseTableName . '.service.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("MODEL_ATTRIBUTES", implode(PHP_EOL, $angular_model_attributes), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $singular_table_name . '.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-detail.component.html');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $singular_table_name . '-detail.component.html'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-detail.component.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $singular_table_name . '-detail.component.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-modal.component.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $singular_table_name . '-modal.component.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/dummy-modal.component.html');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            $file_contents = str_replace("FORM_FIELDS", implode(PHP_EOL, $form_fields), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/' . $singular_table_name . '-modal.component.html'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/app.module.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/app.module.ts'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Angular/app-routing.module.ts');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $singular_table_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Angular/' . $databaseTableName . '/app-routing.module.ts'), $file_contents);

        } else if ($this->option('vue')) {
            $vue_table_headers = $vue_table_headers . '{' . PHP_EOL . 'value: \'actions\',' . PHP_EOL . '},' . PHP_EOL;
            $table_in_kebab_case = $this->toKebabCase($databaseTableName);
            $table_in_pascal_case = $this->toPascalCase($databaseTableName);

            $formsDir = app_path('Console/Commands/Output/Vue/components/forms');
            if (!file_exists($formsDir)) {
                mkdir($formsDir, 0777, true);
            }
            $tablesDir = app_path('Console/Commands/Output/Vue/components/tables');
            if (!file_exists($tablesDir)) {
                mkdir($tablesDir, 0777, true);
            }
            $filtersDir = app_path('Console/Commands/Output/Vue/components/filters');
            if (!file_exists($filtersDir)) {
                mkdir($filtersDir, 0777, true);
            }
            $apiDir = app_path('Console/Commands/Output/Vue/api');
            if (!file_exists($apiDir)) {
                mkdir($apiDir, 0777, true);
            }
            $storeModulesDir = app_path('Console/Commands/Output/Vue/store/modules');
            if (!file_exists($storeModulesDir)) {
                mkdir($storeModulesDir, 0777, true);
            }
            $viewDir = app_path('Console/Commands/Output/Vue/views/' . $table_in_kebab_case);
            if (!file_exists($viewDir)) {
                mkdir($viewDir, 0777, true);
            }

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyForm.vue');
            $file_contents = str_replace("dummysc", $singular_table_name, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            $file_contents = str_replace("VUE_FORM_FIELDS", implode(PHP_EOL, $vue_form_fields), $file_contents);
            $file_contents = str_replace("VUE_FORM_DATA_ATTRIBUTES", $vue_form_data_attributes, $file_contents);
            $file_contents = str_replace("VUE_FORM_IMPORTS", $vue_form_imports, $file_contents);
            $file_contents = str_replace("VUE_FORM_COMPONENTS", $vue_form_components, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/components/forms/' . $model_name . 'Form.vue'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyTable.vue');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            $file_contents = str_replace("VUE_TABLE_HEADERS", $vue_table_headers, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/components/tables/' . $model_name . 'Table.vue'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/DummyFilter.vue');
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/components/filters/' . $model_name . 'Filter.vue'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/Dummys.vue');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("dummysc", $singular_table_name, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/views/' . $table_in_kebab_case . '/' . $table_in_pascal_case . '.vue'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/CreateDummy.vue');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            $file_contents = str_replace("DUMMY", strtoupper($singular_table_name), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/views/' . $table_in_kebab_case . '/Create' . $model_name . '.vue'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/EditDummy.vue');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            $file_contents = str_replace("DUMMY", strtoupper($singular_table_name), $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/views/' . $table_in_kebab_case . '/Edit' . $model_name . '.vue'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/dummy-service.js');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/api/' . $model_in_kebab_case . '-service.js'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/dummys-module.js');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("dummysc", $singular_table_name, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("DUMMY", strtoupper($singular_table_name), $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/store/modules/' . $model_in_kebab_case . 's-module.js'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/routes.js');
            $file_contents = str_replace("dummykc", $model_in_kebab_case, $file_contents);
            $file_contents = str_replace("Dummy", $model_name, $file_contents);
            $file_contents = str_replace("dummy", $model_in_camel_case, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/' . $model_in_kebab_case . '-routes.js'), $file_contents);

            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/README.md');
            file_put_contents(app_path('Console/Commands/Output/Vue/README.md'), $file_contents);

            $vue_translations = $vue_translations .
                '"' . $singular_table_name . '": "",' . PHP_EOL .
                '"' . $databaseTableName . '": "",' . PHP_EOL .
                '"' . $singular_table_name . '_created": "",' . PHP_EOL .
                '"' . $singular_table_name . '_updated": "",' . PHP_EOL .
                '"' . $singular_table_name . '_deleted": "",' . PHP_EOL .
                '"create_' . $singular_table_name . '": "",' . PHP_EOL .
                '"new_' . $singular_table_name . '": "",' . PHP_EOL .
                '"edit_' . $singular_table_name . '": "",' . PHP_EOL .
                '"confirm_' . $singular_table_name . '_delete": ""';
            $file_contents = file_get_contents(__DIR__ . '/Templates/Vue/translations.json');
            $file_contents = str_replace("VUE_TRANSLATIONS", $vue_translations, $file_contents);
            file_put_contents(app_path('Console/Commands/Output/Vue/' . $model_in_kebab_case . '-translations.json'), $file_contents);

        } else {
            //GENERATE BACK-END CODE START
            (new LaravelCodeGenerator($databaseTableName, $databaseTableColumns))->generateLaravelCodeFromNewlyCreatedTable();
        }

        $this->info("Code generated succesfully!");

        //GENERATE BACK-END CODE END

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

    private function getVueTextField(string $field): string
    {
        $result =
            '<v-col cols="12" sm="6">' . PHP_EOL .
            '<v-text-field' . PHP_EOL .
            'v-model="item.' . $field . '"' . PHP_EOL .
            ':error-messages="errors[\'' . $field . '\']"' . PHP_EOL;

        $result = $result .
            ':label="$t(\'' . $field . '\')"' . PHP_EOL .
            '@input="formMixin_clearErrors(\'' . $field . '\')"' . PHP_EOL .
            '/>' . PHP_EOL .
            '</v-col>' . PHP_EOL;

        return $result;
    }

    private function getVueNumberField(string $field): string
    {
        $result =
            '<v-col cols="12" sm="6">' . PHP_EOL .
            '<v-text-field' . PHP_EOL .
            'v-model.number="item.' . $field . '"' . PHP_EOL .
            ':error-messages="errors[\'' . $field . '\']"' . PHP_EOL;

        $result = $result .
            ':label="$t(\'' . $field . '\')"' . PHP_EOL .
            'type="number"' . PHP_EOL .
            '@input="formMixin_clearErrors(\'' . $field . '\')"' . PHP_EOL .
            '/>' . PHP_EOL .
            '</v-col>' . PHP_EOL;

        return $result;
    }

    private function getVueTextArea(string $field): string
    {
        $result =
            '<v-col cols="12">' . PHP_EOL .
            '<v-textarea' . PHP_EOL .
            'v-model="item.' . $field . '"' . PHP_EOL .
            ':error-messages="errors[\'' . $field . '\']"' . PHP_EOL;

        $result = $result .
            ':label="$t(\'' . $field . '\')"' . PHP_EOL .
            'rows="1"' . PHP_EOL .
            'auto-grow' . PHP_EOL .
            '@input="formMixin_clearErrors(\'' . $field . '\')"' . PHP_EOL .
            '/>' . PHP_EOL .
            '</v-col>' . PHP_EOL;

        return $result;
    }

    private function getVueAutocompleteField(string $id_field, string $object_field): string
    {
        $result =
            '<v-col cols="12" sm="6">' . PHP_EOL .
            '<BaseAutocomplete' . PHP_EOL .
            'v-model="item.' . $id_field . '"' . PHP_EOL .
            ':initial-item="item.' . $object_field . '"' . PHP_EOL .
            ':search-function="' . $object_field . 'SearchFunction"' . PHP_EOL .
            ':error-messages="errors.' . $id_field . '"' . PHP_EOL .
            ':label="$t(\'' . $object_field . '\')"' . PHP_EOL .
            'item-text="name"' . PHP_EOL .
            'item-value="id"' . PHP_EOL .
            'clearable' . PHP_EOL .
            '@input="formMixin_clearErrors(\'' . $id_field . '\')"' . PHP_EOL .
            '/>' . PHP_EOL .
            '</v-col>' . PHP_EOL;

        return $result;
    }

    private function getVueCheckboxField(string $field): string
    {
        $result =
            '<v-col cols="12" sm="6">' . PHP_EOL .
            '<v-checkbox' . PHP_EOL .
            'v-model="item.' . $field . '"' . PHP_EOL .
            ':error-messages="errors[\'' . $field . '\']"' . PHP_EOL;

        $result = $result .
            ':label="$t(\'' . $field . '\')"' . PHP_EOL .
            '@change="formMixin_clearErrors(\'' . $field . '\')"' . PHP_EOL .
            '/>' . PHP_EOL .
            '</v-col>' . PHP_EOL;

        return $result;
    }

    private function getVueDateField(string $field): string
    {
        return
            '<v-col cols="12" sm="6">' . PHP_EOL .
            '<BaseDatepickerInput' . PHP_EOL .
            'v-model="item.' . $field . '"' . PHP_EOL .
            ':error-messages="errors[\'' . $field . '\']"' . PHP_EOL .
            ':label="$t(\'' . $field . '\')"' . PHP_EOL .
            '@input="formMixin_clearErrors(\'' . $field . '\')"' . PHP_EOL .
            '/>' . PHP_EOL .
            '</v-col>' . PHP_EOL;
    }

    private function getAngularFormField(string $field, string $singular_table_name): string
    {
        return
            '<div class="form-group">' . PHP_EOL .
            '<label>' . ucfirst($field) . '</label>' . PHP_EOL .
            '<input type="text" class="form-control" [(ngModel)]="' . $singular_table_name . '.' . $field . '" placeholder="' . ucfirst($field) . '">' . PHP_EOL .
            '</div>';
    }

    private function toPascalCase(string $str): string
    {
        return str_replace('_', '', ucwords($str, '_'));
    }

    private function toCamelCase(string $str): string
    {
        return str_replace('_', '', lcfirst(ucwords($str, '_')));
    }

    private function toKebabCase(string $str): string
    {
        return str_replace('_', '-', strtolower($str));
    }
}
