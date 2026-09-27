<?php

namespace Garno\PoweredBy\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Registers <x-garno-badge /> and the publishable browser assets.
 *
 * Auto-discovered by Laravel through composer.json's extra.laravel. Publish the
 * script and images once per project:
 *
 *   php artisan vendor:publish --tag=garno-badge
 */
class BadgeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $root = dirname(__DIR__, 2);

        $this->loadViewsFrom($root . '/resources/views', 'garno-badge');

        Blade::component('garno-badge', Badge::class);

        $this->publishes([
            $root . '/powered-by-garno.js' => public_path('vendor/garno/powered-by-garno.js'),
            $root . '/powered-by-garno-light.svg' => public_path('vendor/garno/powered-by-garno-light.svg'),
            $root . '/powered-by-garno-dark.svg' => public_path('vendor/garno/powered-by-garno-dark.svg'),
        ], 'garno-badge');
    }
}
