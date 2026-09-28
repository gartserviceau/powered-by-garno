<?php

namespace Garno\PoweredBy;

/**
 * The GARNO attribution as plain HTML: a line of text in the client's footer
 * with "GARNO" linking to the GARNO website.
 *
 *   Attribution::render();                         // Built by GARNO
 *   Attribution::render(['mode' => 'maintained']); // Maintained by GARNO
 *   Attribution::render(['mode' => 'custom', 'text' => 'Website support by']);
 *
 * No framework, no script, no styles: it inherits the footer's typography.
 * The WordPress plugin and the Laravel component both render through this.
 */
final class Attribution
{
    public const URL = 'https://garno.com.au/';

    public const NAME = 'GARNO';

    /** Predefined wording, keyed by mode. */
    public const MODES = [
        'built' => 'Built by',
        'improved' => 'Improved by',
        'maintained' => 'Maintained by',
        // Deprecated: GARNO is not the platform a site runs on. Kept so
        // integrations from 1.x that ask for it explicitly keep working.
        'powered' => 'Powered by',
    ];

    public const DEFAULT_MODE = 'built';

    /**
     * @param array{
     *     mode?: string,
     *     text?: string,
     *     url?: string,
     *     show?: bool|string|int,
     *     class?: string,
     *     target?: string,
     *     rel?: string,
     *     href?: string,
     * } $options `href` is the 1.x name for `url`. The 1.x `theme` and `size`
     *            options are accepted and ignored.
     */
    public static function render(array $options = []): string
    {
        if (! self::truthy($options['show'] ?? true)) {
            return '';
        }

        [$before, $after] = self::wording((string) ($options['mode'] ?? ''), (string) ($options['text'] ?? ''));

        $url = self::url((string) ($options['url'] ?? $options['href'] ?? ''));
        $target = ($options['target'] ?? '') === '_blank' ? '_blank' : '';

        $rel = self::tokens(strtolower((string) ($options['rel'] ?? '')));
        if ($target === '_blank' && ! in_array('noopener', $rel, true)) {
            $rel[] = 'noopener';
        }

        $class = implode(' ', array_unique(['garno-attribution', ...self::tokens((string) ($options['class'] ?? ''), '/^[A-Za-z0-9_-]+$/')]));

        $link = '<a href="' . self::e($url) . '"'
            . ($target ? ' target="_blank"' : '')
            . ($rel ? ' rel="' . self::e(implode(' ', $rel)) . '"' : '')
            . ($target ? ' aria-label="' . self::NAME . ' (opens in a new tab)"' : '')
            . '>' . self::NAME . '</a>';

        return '<span class="' . self::e($class) . '">' . self::e($before) . $link . self::e($after) . '</span>';
    }

    /**
     * The text before and after the linked name. Custom text links its first
     * "GARNO", or has " GARNO" appended when it does not mention it.
     *
     * @return array{string, string}
     */
    public static function wording(string $mode, string $text = ''): array
    {
        $mode = strtolower(trim($mode));
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? '');

        if ($mode === 'custom' && $text !== '') {
            $at = strpos($text, self::NAME);

            return $at === false
                ? [$text . ' ', '']
                : [substr($text, 0, $at), substr($text, $at + strlen(self::NAME))];
        }

        return [(self::MODES[$mode] ?? self::MODES[self::DEFAULT_MODE]) . ' ', ''];
    }

    /** Only http(s) URLs; anything else falls back to the GARNO website. */
    private static function url(string $url): string
    {
        $url = trim($url);

        return preg_match('#^https?://[^\s"<>]+$#i', $url) ? $url : self::URL;
    }

    /**
     * Space-separated tokens that match $pattern; the rest are dropped.
     *
     * @return list<string>
     */
    private static function tokens(string $value, string $pattern = '/^[a-z-]+$/'): array
    {
        $tokens = preg_split('/\s+/', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_filter($tokens, fn (string $t): bool => (bool) preg_match($pattern, $t))));
    }

    private static function truthy(mixed $value): bool
    {
        if (is_string($value)) {
            return ! in_array(strtolower(trim($value)), ['0', 'false', 'no', 'off', ''], true);
        }

        return (bool) $value;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
