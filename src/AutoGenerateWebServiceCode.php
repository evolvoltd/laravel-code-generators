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
        $file_contents = file_get_contents(base_path('config/logging.php'));
        $newElement =
            "        'web-service-" . $this->toKebabCase($webserviceName) . "-data' => [
             'driver' => 'daily',
             'path' => storage_path('logs/web-service-" . $this->toKebabCase($webserviceName) . "-data.log'),
             'level' => env('LOG_LEVEL', 'debug'),
             'days' => 14,
       ]";
        if (preg_match("/'emergency'\s*=>\s*\[(.*?)\]/s", $file_contents, $matches)) {

            // Add a new element to the 'ses' array

            $replacement = "$1,\n\n$newElement";
            $file_contents = preg_replace("/('emergency'\s*=>\s*\[(.*?)\])/s", $replacement, $file_contents);

            // Output the new string
        } else {
            $file_contents = substr_replace($file_contents, "", -6);
            $file_contents .= "\n" . $newElement . "\n],\n];";
        }
        file_put_contents(base_path('config/logging.php'), $file_contents);

        //generate config service
        $file_contents = file_get_contents(base_path('config/services.php'));
        
        if (preg_match("/'ses'\s*=>\s*\[(.*?)\]/s", $file_contents, $matches)) {

            $newElement =
        "     '" .$this->toCamelCase($webserviceName) . "' => [
         'connection_url' => env('" . strtoupper($webserviceName) . "_CONNECTION_URL')
    ]";
            $replacement = "$1,\n\n$newElement";
            $file_contents = preg_replace("/('ses'\s*=>\s*\[(.*?)\])/s", $replacement, $file_contents);

        } else {
            $file_contents = str_replace("];", "\n    '" .$this->toCamelCase($webserviceName) . "' => [
          'connection_url' => env('" . strtoupper($webserviceName) . "_CONNECTION_URL')
    ]
];", $file_contents);
        }
        file_put_contents(base_path('config/services.php'),$file_contents);
        
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

    private function toCamelCase(string $str): string
    {
        return str_replace('_', '', lcfirst(ucwords($str, '_')));
    }

    private function toKebabCase(string $str): string
    {
        return str_replace('_', '-', strtolower($str));
    }
}
