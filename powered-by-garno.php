<?php

/**
 * Plugin Name:       Powered by Garno
 * Description:       Adds the "Powered by Garno" credit. Use the [powered_by_garno] shortcode or the garno_badge() template tag where the theme's footer should show it.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.0
 * Author:            Garno
 * Author URI:        https://garno.com.au/
 * License:           Proprietary
 *
 * Install: copy this whole folder into wp-content/plugins/ and activate it,
 * then place the badge with the shortcode or the template tag. The script is only loaded on
 * pages that actually render a badge.
 */

defined('ABSPATH') || exit;

const GARNO_BADGE_VERSION = '1.0.0';

/**
 * The badge markup. The inner link is the no-JavaScript fallback.
 *
 * @param array{theme?: string, size?: string, href?: string, target?: string, rel?: string} $args
 */
function garno_badge_html(array $args = []): string
{
    $args = shortcode_atts([
        'theme' => 'inherit',
        'size' => 'sm',
        'href' => '',
        'target' => '_blank',
        'rel' => '',
    ], $args, 'powered_by_garno');

    $theme = in_array($args['theme'], ['inherit', 'light', 'dark'], true) ? $args['theme'] : 'inherit';
    $size = in_array($args['size'], ['sm', 'md'], true) ? $args['size'] : 'sm';
    $target = $args['target'] === '_self' ? '_self' : '_blank';
    $href = $args['href'] !== '' ? esc_url($args['href']) : 'https://garno.com.au/';

    wp_enqueue_script('powered-by-garno');

    return sprintf(
        '<powered-by-garno theme="%1$s" size="%2$s" target="%3$s"%4$s%7$s><a href="%5$s"%6$s>Powered by Garno</a></powered-by-garno>',
        esc_attr($theme),
        esc_attr($size),
        esc_attr($target),
        $args['href'] !== '' ? ' href="' . esc_attr($href) . '"' : '',
        esc_attr($href),
        $target === '_blank' ? ' target="_blank" rel="noopener"' : '',
        $args['rel'] !== '' ? ' rel="' . esc_attr($args['rel']) . '"' : '',
    );
}

/**
 * Template tag for theme footers: <?php garno_badge(['theme' => 'dark']); ?>
 */
function garno_badge(array $args = []): void
{
    echo garno_badge_html($args); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
}

add_action('wp_enqueue_scripts', function (): void {
    wp_register_script(
        'powered-by-garno',
        plugins_url('powered-by-garno.js', __FILE__),
        [],
        GARNO_BADGE_VERSION,
        ['in_footer' => true, 'strategy' => 'defer'],
    );
});

// The file is an ES module (it exports the element class).
add_filter('script_loader_tag', function (string $tag, string $handle): string {
    return $handle === 'powered-by-garno'
        ? str_replace('<script ', '<script type="module" ', $tag)
        : $tag;
}, 10, 2);

add_shortcode('powered_by_garno', fn ($atts): string => garno_badge_html((array) $atts));
