<?php

namespace Gilcleis\Support;

use Illuminate\Support\ServiceProvider;
use Gilcleis\Support\Commands\MakeEntityCommand;

class EntityGeneratorServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([
            MakeEntityCommand::class
        ]);

        $this->publishes([
            __DIR__ . '/../config/entity-generator.php' => config_path('entity-generator.php'),
        ], 'config');

        $this->loadViewsFrom(__DIR__ . '/../stubs', 'entity-generator');
    }
}
