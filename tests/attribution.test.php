<?php

/**
 * Tests for the PHP renderer, the WordPress plugin and the Laravel component.
 * Dependency-free: WordPress and Laravel are stubbed with the few functions
 * and classes the package touches.
 *
 *   php tests/attribution.test.php
 */

declare(strict_types=1);

// WordPress stubs.
define('ABSPATH', __DIR__);
$shortcodes = [];
function shortcode_atts(array $pairs, array $atts, string $shortcode = ''): array
{
    $out = [];
    foreach ($pairs as $name => $default) {
        $out[$name] = array_key_exists($name, $atts) ? $atts[$name] : $default;
    }

    return $out;
}
function add_shortcode(string $tag, callable $callback): void
{
    $GLOBALS['shortcodes'][$tag] = $callback;
}

// Laravel stubs.
eval('namespace Illuminate\View; abstract class Component {}');
eval('namespace Illuminate\Contracts\View; interface View {}');

require __DIR__ . '/../src/Attribution.php';
require __DIR__ . '/../src/Laravel/Badge.php';
require __DIR__ . '/../powered-by-garno.php';

use Garno\PoweredBy\Attribution;
use Garno\PoweredBy\Laravel\Badge;

$failures = 0;
$count = 0;
function check(string $name, string $expected, string $actual): void
{
    global $failures, $count;
    $count++;
    if ($expected === $actual) {
        echo "ok   $name\n";

        return;
    }
    $failures++;
    echo "FAIL $name\n  expected: $expected\n  actual:   $actual\n";
}

$link = '<a href="https://garno.com.au/">GARNO</a>';
$wrap = fn (string $inner, string $class = 'garno-attribution'): string => "<span class=\"$class\">$inner</span>";

// Modes.
check('default mode is Built by GARNO', $wrap("Built by $link"), Attribution::render());
check('built', $wrap("Built by $link"), Attribution::render(['mode' => 'built']));
check('improved', $wrap("Improved by $link"), Attribution::render(['mode' => 'improved']));
check('maintained', $wrap("Maintained by $link"), Attribution::render(['mode' => 'maintained']));
check('mode is case-insensitive', $wrap("Maintained by $link"), Attribution::render(['mode' => ' Maintained ']));
check('unknown mode falls back to built', $wrap("Built by $link"), Attribution::render(['mode' => 'hosted']));
check('deprecated powered mode still works', $wrap("Powered by $link"), Attribution::render(['mode' => 'powered']));

// Custom text.
check('custom text gets GARNO appended', $wrap("Website support by $link"), Attribution::render(['mode' => 'custom', 'text' => 'Website support by']));
check('custom text links its own GARNO', $wrap("Designed with $link in Perth"), Attribution::render(['mode' => 'custom', 'text' => 'Designed with GARNO in Perth']));
check('custom text whitespace is collapsed', $wrap("Hosted by $link"), Attribution::render(['mode' => 'custom', 'text' => "  Hosted \n by  "]));
check('custom without text falls back to built', $wrap("Built by $link"), Attribution::render(['mode' => 'custom', 'text' => ' ']));
check('text is ignored outside custom mode', $wrap("Improved by $link"), Attribution::render(['mode' => 'improved', 'text' => 'Other']));
check(
    'custom text is escaped',
    $wrap("&lt;img src=x onerror=&quot;alert(1)&quot;&gt; &amp; by $link"),
    Attribution::render(['mode' => 'custom', 'text' => '<img src=x onerror="alert(1)"> & by']),
);

// URL.
check('configurable url', $wrap('Built by <a href="https://garno.example/?a=1&amp;b=2">GARNO</a>'), Attribution::render(['url' => 'https://garno.example/?a=1&b=2']));
check('javascript: url is rejected', $wrap("Built by $link"), Attribution::render(['url' => 'javascript:alert(1)']));
check('url with quotes is rejected', $wrap("Built by $link"), Attribution::render(['url' => 'https://x.test/"onmouseover="alert(1)']));
check('1.x href is read as url', $wrap('Built by <a href="https://garno.example/">GARNO</a>'), Attribution::render(['href' => 'https://garno.example/']));

// Visibility.
check('show=false renders nothing', '', Attribution::render(['show' => false]));
check('show="false" renders nothing', '', Attribution::render(['show' => 'false']));
check('show="0" renders nothing', '', Attribution::render(['show' => '0']));
check('show=true renders', $wrap("Built by $link"), Attribution::render(['show' => 'true']));

// Link attributes.
check('no target, rel or nofollow by default', $wrap("Built by $link"), Attribution::render());
check(
    'target=_blank adds noopener and a label',
    $wrap('Built by <a href="https://garno.com.au/" target="_blank" rel="noopener" aria-label="GARNO (opens in a new tab)">GARNO</a>'),
    Attribution::render(['target' => '_blank']),
);
check('other targets are ignored', $wrap("Built by $link"), Attribution::render(['target' => 'evil"']));
check('rel is passed through', $wrap('Built by <a href="https://garno.com.au/" rel="nofollow">GARNO</a>'), Attribution::render(['rel' => 'NoFollow']));
check('rel junk is dropped', $wrap("Built by $link"), Attribution::render(['rel' => '"><script>']));

// Styling hook.
check('class is added', $wrap("Built by $link", 'garno-attribution site-footer__credit'), Attribution::render(['class' => 'site-footer__credit']));
check('class junk is dropped', $wrap("Built by $link", 'garno-attribution ok'), Attribution::render(['class' => 'ok "><x']));

// Theme and size from 1.x are ignored.
check('1.x theme and size are ignored', $wrap("Built by $link"), Attribution::render(['theme' => 'dark', 'size' => 'md']));

// WordPress.
check('wp: garno_attribution_html default', $wrap("Built by $link"), garno_attribution_html());
check('wp: shortcode', $wrap("Maintained by $link"), $shortcodes['garno_attribution'](['mode' => 'maintained']));
check('wp: shortcode show="false"', '', $shortcodes['garno_attribution'](['show' => 'false']));
check('wp: 1.x shortcode now says Built by', $wrap("Built by $link"), $shortcodes['powered_by_garno'](['theme' => 'dark']));
check('wp: 1.x shortcode href', $wrap('Built by <a href="https://garno.example/">GARNO</a>'), $shortcodes['powered_by_garno'](['href' => 'https://garno.example/']));
check('wp: 1.x garno_badge_html', $wrap("Improved by $link"), garno_badge_html(['mode' => 'improved']));
ob_start();
garno_badge(['theme' => 'dark']);
check('wp: 1.x garno_badge echoes', $wrap("Built by $link"), (string) ob_get_clean());

// Laravel.
check('laravel: default', $wrap("Built by $link"), (new Badge())->html());
check('laravel: props and class', $wrap("Maintained by $link", 'garno-attribution text-sm'), (new Badge(mode: 'maintained'))->html('text-sm'));
check('laravel: custom', $wrap("Support by $link"), (new Badge(mode: 'custom', text: 'Support by'))->html());
check('laravel: 1.x href/theme/size', $wrap('Built by <a href="https://garno.example/">GARNO</a>'), (new Badge(href: 'https://garno.example/', theme: 'dark', size: 'md'))->html());
check('laravel: show=false does not render', 'false', var_export((new Badge(show: false))->shouldRender(), true));

echo "\n$count checks, $failures failed\n";
exit($failures ? 1 : 0);
