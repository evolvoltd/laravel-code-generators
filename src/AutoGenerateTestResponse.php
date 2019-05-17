<?php

namespace Evolvo\LaravelCodeGenerators;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class AutoGenerateTestResponse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:test-response {test_name} {method?}' ;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate test response for given test';

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
     * @param $results
     * @param $key
     * @return mixed
     */
    public static function makeStructure(&$results, $key)
    {
        $structurePost = str_replace('":', '" => ', $results[$key]??'');
        $structurePost = str_replace("{", "[\n", $structurePost);
        $structurePost = str_replace("}", ",\n]\n", $structurePost);
        $structurePost = str_replace(",", ",\n", $structurePost);
        $structurePost = str_replace(".HTTP/1.1 200 OK", "", $structurePost);
        $structurePost = str_replace(".....", "", $structurePost);
        $structurePost = str_replace("7 / 7 (100%)", "", $structurePost);
        
        return $structurePost;
    }


    public function handle()
    {
        $methodName = $this->argument('method');
        $testNamePath = $this->argument('test_name');
        
        $file_contents = file_get_contents(base_path('tests/Feature/' . $testNamePath . '.php'));

        $testName = explode('/',$testNamePath);
        $testName = end($testName);
        
        if(!$methodName) {

            $name = substr($testName, 0, -4);
            $testMethodNames = [
                'test' . $name . 'Store',
                'test' . $name . 'Update',
                'test' . $name . 'Index',
                'test' . $name . 'Show'
            ];
            $this->info(json_encode($testMethodNames));

            $bar = $this->output->createProgressBar(6);
            $bar->advance();

            foreach ($testMethodNames as $method) {
                $process = new Process('./vendor/bin/phpunit --filter ' . $method . ' --configuration ' . base_path() . '/phpunit.xml');
                $process->run();

                $results[] = explode("\n", $process->getOutput());
                echo $process->getOutput();
                $bar->advance();
            }


            $file_contents = str_replace("[/*'post_json'*/]", self::makeStructure($results[0], 9), $file_contents);
            $file_contents = str_replace("[/*'put_json'*/]", self::makeStructure($results[1], 9), $file_contents);
            $file_contents = str_replace("[/*'index_json'*/]", self::makeStructure($results[2], 9), $file_contents);
            $file_contents = str_replace("[/*'show_json'*/]", self::makeStructure($results[3], 9), $file_contents);
            //$file_contents = str_replace("[/*'custom_json'*/]", self::makeStructure($results[3], 9), $file_contents);


            file_put_contents(base_path('tests/Feature/' . $testNamePath . '.php'), $file_contents);

            $this->info($testName . " test json outputs created!");
            $bar->finish();
        }

        else {

            $process = new Process('./vendor/bin/phpunit --filter ' . $methodName . ' --configuration ' . base_path() . '/phpunit.xml');
            $process->run();

            $results[] = explode("\n", $process->getOutput());
            echo $process->getOutput();

            $files = str_replace("[/*'custom_json'*/]", self::makeStructure($results[0], 9), $file_contents);
            file_put_contents(base_path('tests/Feature/' . $testName . '.php'), $files);
            $this->info($testName . " test json output created!");
        }
    }
}
