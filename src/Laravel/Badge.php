<?php

namespace Garno\PoweredBy\Laravel;

use Garno\PoweredBy\Attribution;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * <x-garno-attribution mode="maintained" />
 *
 * Renders the GARNO attribution as plain HTML: no script, no assets. Any
 * `class` given on the tag is added to the wrapper. Also registered as the
 * deprecated 1.x <x-garno-badge />, whose `theme` and `size` are ignored.
 */
class Badge extends Component
{
    public function __construct(
        public string $mode = Attribution::DEFAULT_MODE,
        public ?string $text = null,
        public ?string $url = null,
        public bool $show = true,
        public ?string $target = null,
        public ?string $rel = null,
        ?string $href = null, // 1.x name for url.
        ?string $theme = null, // 1.x, ignored.
        ?string $size = null, // 1.x, ignored.
    ) {
        $this->url ??= $href;
    }

    public function html(?string $class = null): string
    {
        return Attribution::render([
            'mode' => $this->mode,
            'text' => (string) $this->text,
            'url' => (string) $this->url,
            'show' => $this->show,
            'class' => (string) $class,
            'target' => (string) $this->target,
            'rel' => (string) $this->rel,
        ]);
    }

    public function shouldRender(): bool
    {
        return $this->show;
    }

    public function render(): View
    {
        return view('garno-badge::attribution');
    }
}
