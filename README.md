# GARNO attribution

A small, optional credit for the footer of websites [GARNO](https://garno.com.au/)
has worked on:

```
© 2026 Client Business. All rights reserved.    Built by GARNO
```

"GARNO" links to the GARNO website. Everything else is plain text in the
client's own font and colours.

## When to show it

The attribution is **optional**. Only show it where it has been agreed with the
client, and pick the mode that honestly describes GARNO's part in the site:

| Mode | Renders | Use when |
|------|---------|----------|
| `built` (default) | Built by GARNO | GARNO designed and developed the site, or did the substantial implementation. |
| `improved` | Improved by GARNO | Someone else built the site and GARNO did significant work on it: a redesign, performance or technical upgrade, migration, or development work. |
| `maintained` | Maintained by GARNO | GARNO provides ongoing maintenance and support but did not build the site. |
| `custom` | Your `text` | None of the above fits, e.g. `Website support by GARNO`. |
| `powered` | Powered by GARNO | **Deprecated.** GARNO is not the platform a site runs on. Kept only so 1.x integrations that ask for it keep working. |

Do not add it to garno.com.au itself: "Built by GARNO" on GARNO's own site is
redundant.

Keep the wording plain. No keyword links ("Best Web Design Perth by GARNO"), no
logos, badges or animation: the credit should never compete with the client's
own brand.

## Options

The same options everywhere:

| Option | Values | Default |
|--------|--------|---------|
| `mode` | `built`, `improved`, `maintained`, `custom` | `built` |
| `text` | Wording for `mode="custom"`. Its first `GARNO` becomes the link; without one, ` GARNO` is appended. | none |
| `url` | Where "GARNO" links (`http`/`https` only) | `https://garno.com.au/` |
| `show` | `false` renders nothing | `true` |
| `class` | Extra classes on the wrapper, for the host's CSS | none |
| `target` | `_blank` to open a new tab (adds `rel="noopener"`) | same tab |
| `rel` | Extra relations, e.g. `nofollow` if the client asks | none |

The output is always:

```html
<span class="garno-attribution">Built by <a href="https://garno.com.au/">GARNO</a></span>
```

All text and attributes are escaped. There is no script, stylesheet, font or
image to load, and nothing is requested from anywhere.

**Theming.** It inherits the footer's typography. Style it like any other
footer text, e.g. `.garno-attribution { opacity: .75 }` or
`.garno-attribution a { color: inherit }`.

## WordPress

**With Composer (Bedrock).** Add the repository once, then require it.
Bedrock's `composer/installers` puts it in `web/app/plugins/`:

```json
"repositories": [
  { "type": "vcs", "url": "https://github.com/gartserviceau/powered-by-garno" }
]
```

```bash
composer require garno/powered-by-garno:^2.0
wp plugin activate powered-by-garno
```

**Without Composer.** Download `powered-by-garno.zip` from the latest
[release](https://github.com/gartserviceau/powered-by-garno/releases) and upload
it under *Plugins → Add New → Upload Plugin*.

**Placing it** in the client's footer:

- Footer widget or block: `[garno_attribution]` or `[garno_attribution mode="maintained"]`
- Theme template: `<?php garno_attribution(['mode' => 'improved']); ?>`
- Sage/Blade: `@php(garno_attribution())`
- Custom: `[garno_attribution mode="custom" text="Website support by"]`

`garno_attribution_html($args)` returns the markup instead of echoing it.

## Laravel

Add the repository to `composer.json` as above, then:

```bash
composer require garno/powered-by-garno:^2.0
```

The service provider is auto-discovered. In the footer:

```blade
<x-garno-attribution />
<x-garno-attribution mode="maintained" class="text-sm" />
<x-garno-attribution mode="custom" text="Website support by" />
<x-garno-attribution :show="config('app.show_credit')" />
```

Pass booleans with `:show`, since `show="false"` is a non-empty string.

## Plain HTML, React, Vue, anything else

No package is needed. Paste the markup into the footer:

```html
<span class="garno-attribution">Maintained by <a href="https://garno.com.au/">GARNO</a></span>
```

```jsx
<span className="garno-attribution">Built by <a href="https://garno.com.au/">GARNO</a></span>
```

**Optional custom element.** Where the footer is hand-written HTML and it is
easier to switch a mode than edit text, `powered-by-garno.js` (~2 kB gzipped, no
dependencies) defines `<garno-attribution>` with the same options as
attributes. It renders into the light DOM, so it inherits the footer's styles.
Put the credit inside the tag as the no-JavaScript fallback:

```html
<script src="/powered-by-garno.js" defer></script>

<garno-attribution mode="improved">Improved by <a href="https://garno.com.au/">GARNO</a></garno-attribution>
```

```bash
npm install github:gartserviceau/powered-by-garno#v2.0.0
```

```js
import 'powered-by-garno';
```

In Next.js / Nuxt, import it in a client-only component; the server renders
the fallback.

## Links and SEO

The link is a plain branded anchor ("GARNO"), which is what search engines
expect from a site credit. `nofollow` is not added by default; add
`rel="nofollow"` only if the client asks for it.

## Upgrading from 1.x ("Powered by Garno")

2.0 replaces the animated logo badge with a text attribution and changes the
default wording to **Built by GARNO**. Existing integrations keep working
without changes, but now render the new text:

| 1.x | 2.x | Notes |
|-----|-----|-------|
| `[powered_by_garno]` | `[garno_attribution]` | Old shortcode still works (deprecated). |
| `garno_badge()` / `garno_badge_html()` | `garno_attribution()` / `garno_attribution_html()` | Old functions still work (deprecated). |
| `<x-garno-badge />` | `<x-garno-attribution />` | Old component still works (deprecated). |
| `<powered-by-garno>` | `<garno-attribution>` | Old element still works (deprecated). |
| `href` | `url` | `href` is still read. |
| `theme`, `size` | none | Accepted and ignored; style with CSS instead. |
| `target` default `_blank` | same tab | Pass `target="_blank"` to keep a new tab. |
| "Powered by" wording | `mode="powered"` | Deprecated; pick an accurate mode instead. |

The badge images (`powered-by-garno-*.svg`) and Laravel's
`vendor:publish --tag=garno-badge` step are gone; delete any
`public/vendor/garno/` copies. The default link no longer carries UTM
parameters.

## Tests

```bash
npm test
```

Runs the JavaScript tests (`node --test`, including a check that the element
and the PHP renderer produce the same markup) and the PHP tests, which stub
WordPress and Laravel so neither is needed.

## Releasing a new version

1. Bump the version in `powered-by-garno.php` (header and
   `GARNO_ATTRIBUTION_VERSION`), `powered-by-garno.js` (`VERSION`) and
   `package.json`. `bin/check-version.sh` checks they agree; CI runs it on
   every push.
2. Commit, then tag and push:

   ```bash
   git tag v2.0.1 && git push origin main v2.0.1
   ```

3. The release workflow checks the tag against the code, builds
   `powered-by-garno.zip` and publishes the GitHub release. Composer and npm
   installs pick up the tag directly.

## Demo

Open `demo.html` in a browser.
