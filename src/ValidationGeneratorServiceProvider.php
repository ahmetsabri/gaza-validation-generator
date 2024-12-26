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
        //
    }
}
