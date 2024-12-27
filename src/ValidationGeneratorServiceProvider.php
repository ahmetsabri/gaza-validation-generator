<?php

namespace Gaza\ValidationGenerator;

use Gaza\ValidationGenerator\Console\ValidateTableCommand;
use Illuminate\Support\ServiceProvider;

class ValidationGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register()
    {
        $this->commands([
            ValidateTableCommand::class,
        ]);
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../resources/data/validationGeneratorConfig.json' => resource_path('vendor/gaza-validation-generator/validationGeneratorConfig.json'),
        ]);
    }
}
