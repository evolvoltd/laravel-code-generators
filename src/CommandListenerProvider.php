<?php

namespace Evolvo\LaravelCodeGenerators;

use Evolvo\LaravelCodeGenerators\Converters\LaravelConverter;
use Evolvo\LaravelCodeGenerators\Generators\LaravelCodeUpdater;
use Evolvo\LaravelCodeGenerators\MigrationParsers\MigrationParser;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Support\Str;
use function PHPUnit\Framework\throwException;

class CommandListenerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(MigrationsEnded::class, function (MigrationsEnded $event) {
            if ($event->method == 'up' && config('app.env') == 'local') {
                foreach((new MigrationParser())->updatedTables() as $updatedTable){
                    (new LaravelCodeUpdater($updatedTable->tableName, $updatedTable->addedColumns))
                        ->updateCode();
                }
            }
        });
    }
}
