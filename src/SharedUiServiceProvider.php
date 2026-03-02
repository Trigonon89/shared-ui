<?php

namespace Trigonon\SharedUi;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class SharedUiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Add package views to the global view finder (makes @extends('layouts.app') work)
        $this->callAfterResolving('view', function ($view) {
            $view->addLocation(__DIR__ . '/../resources/views');
        });

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'shared-ui');

        Blade::anonymousComponentPath(__DIR__ . '/../resources/views/components');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/shared-ui'),
        ], 'shared-ui-views');
    }
}