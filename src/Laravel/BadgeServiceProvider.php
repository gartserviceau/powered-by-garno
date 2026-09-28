<?php

namespace Garno\PoweredBy\Laravel;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

/**
 * Registers <x-garno-attribution /> (and the deprecated 1.x <x-garno-badge />).
 *
 * Auto-discovered by Laravel through composer.json's extra.laravel. The
 * component renders plain HTML, so there is nothing to publish.
 */
class BadgeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(dirname(__DIR__, 2) . '/resources/views', 'garno-badge');

        Blade::component('garno-attribution', Badge::class);
        Blade::component('garno-badge', Badge::class);
    }
}
