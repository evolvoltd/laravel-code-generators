<?php

namespace Evolvo\LaravelCodeGenerators;

use App\Providers\AuthServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AutoGenerateWebServiceCode extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:webservice {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Auto generate webservice code.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $webserviceName = $this->argument('name');
        $webserviceName = lcfirst($webserviceName);
        $pieces = preg_split('/(?=[A-Z])/', $webserviceName);
        $webserviceName = implode('_', $pieces);


        //generate service
        $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/WebService/Integrations/DummyService.php.tpl');
        $file_contents = str_replace("Dummy", ucfirst($this->toCamelCase($webserviceName)), $file_contents);
        $file_contents = str_replace("WEBSERVICE-NAME", $this->toKebabCase($webserviceName), $file_contents);
        $file_contents = str_replace("WEBSERVICE_NAME", ucfirst($this->toCamelCase($webserviceName)), $file_contents);
        $file_contents = str_replace("webserviceName", $this->toCamelCase($webserviceName), $file_contents);

        $fileName = ucfirst($this->toCamelCase($webserviceName)) . 'Service.php';
        !file_exists(base_path('app/Services/Integrations/')) ? mkdir(base_path('app/Services/Integrations/')) : null;
        file_put_contents(base_path('app/Services/Integrations/' . $fileName), $file_contents);

        //generate config log
        $file_contents = file_get_contents(base_path('Config/logging.php'));

        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);


        $file_contents .= "\n" . "'web-service-" . $this->toKebabCase($webserviceName) . "-data' => [
        'driver' => 'daily',
        'path' => storage_path('logs/web-service-" . $this->toKebabCase($webserviceName) . "-data.log'),
        'level' => env('LOG_LEVEL', 'debug'),
        'days' => 14,
                 ],
    ],
];";

        file_put_contents(base_path('Config/logging.php'), $file_contents);

        //generate config service
        $file_contents = file_get_contents(base_path('Config/services.php'));
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);
        $file_contents = substr_replace($file_contents, "", -1);

        $file_contents .= "\n" . "
        '" . $this->toCamelCase($webserviceName) . "' => [
        'connection_url' => env('" . strtoupper($webserviceName) . "_CONNECTION_URL')
    ],
];
        ";
        file_put_contents(base_path('Config/services.php'), $file_contents);

        $file_contents = file_get_contents(base_path('.env'));
        $file_contents .= strtoupper($webserviceName).'_CONNECTION_URL=';
        file_put_contents(base_path('.env'), $file_contents);
        
        //generate test
        $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/WebService/Tests/DummyServiceTest.php.tpl');
        $file_contents = str_replace("Dummy", ucfirst($this->toCamelCase($webserviceName)), $file_contents);

        $fileName = ucfirst($this->toCamelCase($webserviceName)) . 'ServiceTest.php';
        !file_exists(base_path('tests/Feature/' . ucfirst($this->toCamelCase($webserviceName)))) ? mkdir(base_path('tests/Feature/' . ucfirst($this->toCamelCase($webserviceName)))) : null;
        file_put_contents(base_path('tests/Feature/' . ucfirst($this->toCamelCase($webserviceName)) . '/' . $fileName), $file_contents);


//        generate response exception
        $file_contents = file_get_contents(__DIR__ . '/Templates/Laravel/WebService/Exceptions/ResponseException.php.tpl');
        $file_contents = str_replace("Dummy", ucfirst($this->toCamelCase($webserviceName)), $file_contents);

        !file_exists(base_path('app/Exceptions/' . ucfirst($this->toCamelCase($webserviceName)))) ? mkdir(base_path('app/Exceptions/' . ucfirst($this->toCamelCase($webserviceName)))) : null;
        file_put_contents(base_path('app/Exceptions/' . ucfirst($this->toCamelCase($webserviceName)) . '/ResponseException.php'), $file_contents);

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
