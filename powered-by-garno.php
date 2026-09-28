<?php

/**
 * Plugin Name:       GARNO Attribution
 * Description:       Adds a subtle "Built by GARNO" credit to the footer of sites GARNO has worked on. Use the [garno_attribution] shortcode or the garno_attribution() template tag.
 * Version:           2.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            GARNO
 * Author URI:        https://garno.com.au/
 * License:           Proprietary
 *
 * Install: copy this whole folder into wp-content/plugins/ and activate it,
 * then place the credit with the shortcode or the template tag. It renders
 * plain HTML and loads no scripts, styles or fonts.
 */

defined('ABSPATH') || exit;

const GARNO_ATTRIBUTION_VERSION = '2.0.0';

if (! class_exists(\Garno\PoweredBy\Attribution::class)) {
    require_once __DIR__ . '/src/Attribution.php'; // Installed from the zip, without Composer.
}

/**
 * The attribution markup.
 *
 * @param array{mode?: string, text?: string, url?: string, show?: bool|string, class?: string, target?: string, rel?: string} $args
 */
function garno_attribution_html(array $args = []): string
{
    $args = shortcode_atts([
        'mode' => \Garno\PoweredBy\Attribution::DEFAULT_MODE,
        'text' => '',
        'url' => '',
        'show' => true,
        'class' => '',
        'target' => '',
        'rel' => '',
        'href' => '', // 1.x name for url.
    ], $args, 'garno_attribution');

    $args['url'] = $args['url'] !== '' ? $args['url'] : $args['href'];
    unset($args['href']);

    return \Garno\PoweredBy\Attribution::render($args);
}

/**
 * Template tag for theme footers: <?php garno_attribution(['mode' => 'maintained']); ?>
 */
function garno_attribution(array $args = []): void
{
    echo garno_attribution_html($args); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped by Attribution::render().
}

add_shortcode('garno_attribution', fn ($atts): string => garno_attribution_html((array) $atts));

/*
 * Deprecated 1.x names, kept so existing footers keep working. They now
 * render the text attribution ("Built by GARNO" unless a mode is given)
 * instead of the animated badge; `theme` and `size` are ignored.
 */

/** @deprecated 2.0.0 Use garno_attribution_html(). */
function garno_badge_html(array $args = []): string
{
    return garno_attribution_html($args);
}

/** @deprecated 2.0.0 Use garno_attribution(). */
function garno_badge(array $args = []): void
{
    garno_attribution($args);
}

add_shortcode('powered_by_garno', fn ($atts): string => garno_attribution_html((array) $atts));
