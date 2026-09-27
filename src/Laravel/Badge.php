<?php

namespace Garno\PoweredBy\Laravel;

use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * <x-garno-badge theme="dark" />
 *
 * Renders the <powered-by-garno> element with the static SVG as its no-JS
 * fallback, and includes the script once per page however many badges it has.
 */
class Badge extends Component
{
    public string $theme;

    public string $size;

    public string $target;

    public function __construct(
        string $theme = 'inherit',
        string $size = 'sm',
        public ?string $href = null,
        string $target = '_blank',
        public ?string $rel = null,
    ) {
        $this->theme = in_array($theme, ['inherit', 'light', 'dark'], true) ? $theme : 'inherit';
        $this->size = in_array($size, ['sm', 'md'], true) ? $size : 'sm';
        $this->target = $target === '_self' ? '_self' : '_blank';
    }

    /** The fallback image: dark on dark footers, light everywhere else. */
    public function image(): string
    {
        return asset('vendor/garno/powered-by-garno-' . ($this->theme === 'dark' ? 'dark' : 'light') . '.svg');
    }

    public function render(): View
    {
        return view('garno-badge::badge');
    }
}
