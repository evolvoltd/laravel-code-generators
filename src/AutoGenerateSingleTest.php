<?php

namespace Evolvo\LaravelCodeGenerators;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AutoGenerateSingleTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:single-test {path/test_name} {api_route?} {table?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generates single test';

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
        $testNamePath = $this->argument('path/test_name');
        $route = $this->argument('api_route');
        $table = $this->argument('table');
        $options = $this->options();

        $contents = file_get_contents(base_path('tests/Feature/' . $testNamePath . '.php'));

        $replacement = file_get_contents(__DIR__ . '/Templates/Laravel/DummySingleTest.php.tpl');
        $contents = substr($contents, 0, -2) . $replacement;
        $name = $this->ask('enter test name (E.g. Statistics)');

        $contents = str_replace("testCustom()", "test" . $name . '()' ?? chr(rand(65, 10)) . '()', $contents);

        $request_method = $this->ask('enter request method (post,put,get,delete)');

        $contents = str_replace("/*met*/", $request_method, $contents);


        $route = $this->ask('enter route (api/????)');

        $contents = str_replace("/*uri*/", "api/" . $route, $contents);
        $contents = substr($contents, 0, -1);

        file_put_contents(base_path('tests/Feature/' . $testNamePath . '.php'), $contents);
        
        $this->info('Test created!');
    }

}
