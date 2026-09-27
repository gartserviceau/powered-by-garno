# Powered by Garno

The "Powered by Garno" credit for sites built or looked after by
[Garno](https://garno.com.au/): the Garno mark (the G with the amber arrow)
and the words *Powered by Garno*, as a small animated link.

One package, every platform:

| Site | Install | Use |
|------|---------|-----|
| WordPress + Composer (Bedrock) | `composer require garno/powered-by-garno` | `[powered_by_garno theme="dark"]` or `garno_badge()` |
| WordPress, no Composer | Upload `powered-by-garno.zip` from [Releases](https://github.com/gartserviceau/powered-by-garno/releases) | same |
| Laravel | `composer require garno/powered-by-garno` | `<x-garno-badge theme="dark" />` |
| Node, React, Vue, Next.js | `npm install github:gartserviceau/powered-by-garno#v1.0.0` | `import 'powered-by-garno'` + `<powered-by-garno>` |
| Static HTML | Download the release files | `<script>` + `<powered-by-garno>` |
| No scripts allowed (site builders, PDFs) | `powered-by-garno-dark.svg` / `-light.svg` | a plain `<img>` link |

## What it is

A standard **web component** in one dependency-free file
(`powered-by-garno.js`, ~8 kB, ~3 kB gzipped). Browsers understand custom
elements natively, so no framework adapter is needed.

- **Isolated.** Shadow DOM: the client's CSS cannot break it and its CSS cannot
  leak out.
- **CSP-friendly.** Styles use a constructable stylesheet, which a strict
  `style-src` without `'unsafe-inline'` allows.
- **No third-party requests.** No fonts, no tracking, no remote loading. Ship
  the files with the site; never hot-link them from another domain.
- **Accessible.** One link named "Powered by Garno (opens in a new tab)", a
  visible focus ring, and no animation under `prefers-reduced-motion`.
- **Never a bare link.** The fallback inside the tag is the static badge image,
  so without JavaScript the credit still looks right.
- **Animated.** The arrow shoots through the G once when the badge scrolls into
  view, and nudges forward on hover and keyboard focus. The SVG images play the
  same animation once on load.

## Attributes

| Attribute | Values | Default |
|-----------|--------|---------|
| `theme` | `inherit` (takes the surrounding text colour), `light`, `dark` | `inherit` |
| `size` | `sm` (12px text), `md` (14px) | `sm` |
| `href` | Any URL | `https://garno.com.au/?utm_source=<client host>&utm_medium=powered-by-badge` |
| `target` | `_blank`, `_self` | `_blank` |
| `rel` | Extra relations, e.g. `nofollow` (`noopener` is added for a new tab) | none |

The link is exposed as `::part(link)` for spacing tweaks:
`powered-by-garno::part(link) { padding: 8px; }`.

## WordPress

**With Composer (Bedrock).** Add the repository once, then require it.
Bedrock's `composer/installers` puts it in `web/app/plugins/`:

```json
"repositories": [
  { "type": "vcs", "url": "https://github.com/gartserviceau/powered-by-garno" }
]
```

```bash
composer require garno/powered-by-garno:^1.0
wp plugin activate powered-by-garno
```

**Without Composer.** Download `powered-by-garno.zip` from the latest release
and upload it under *Plugins → Add New → Upload Plugin*.

**Placing it** in the client's footer:

- Footer widget or block: `[powered_by_garno theme="dark"]`
- Theme template: `<?php garno_badge(['theme' => 'dark']); ?>`
- Sage/Blade: `@php(garno_badge(['theme' => 'dark']))`

The script is only loaded on pages that render a badge.

## Laravel

Add the repository to `composer.json` as above, then:

```bash
composer require garno/powered-by-garno:^1.0
php artisan vendor:publish --tag=garno-badge
```

The service provider is auto-discovered. In the footer:

```blade
<x-garno-badge theme="dark" />
```

It includes the script once per page and renders the SVG fallback. Props:
`theme`, `size`, `href`, `target`, `rel`. After upgrading, run
`php artisan vendor:publish --tag=garno-badge --force` to refresh the files.

## Node, React, Vue, Next.js

```bash
npm install github:gartserviceau/powered-by-garno#v1.0.0
```

```js
import 'powered-by-garno';
```

```jsx
<powered-by-garno theme="dark">
  <a href="https://garno.com.au/">
    <img src="/powered-by-garno-dark.svg" alt="Powered by Garno" width="140" height="24" />
  </a>
</powered-by-garno>
```

Copy the SVG from `node_modules/powered-by-garno/` into the app's `public/`.
Next.js / Nuxt: import it in a client-only component (`'use client'` or
`<ClientOnly>`) because `customElements` only exists in the browser; the server
still renders the fallback image.

## Static HTML

```html
<script src="/powered-by-garno.js" defer></script>

<powered-by-garno theme="dark">
  <a href="https://garno.com.au/">
    <img src="/powered-by-garno-dark.svg" alt="Powered by Garno" width="140" height="24">
  </a>
</powered-by-garno>
```

## Image only

Where no script can be added:

```html
<a href="https://garno.com.au/" target="_blank" rel="noopener">
  <img src="powered-by-garno-dark.svg" alt="Powered by Garno" width="140" height="24">
</a>
```

Email clients do not show SVG; convert to PNG for email signatures.

## Links and SEO

The badge links with a branded anchor ("Garno"), which is what search engines
expect from a site credit. If a client prefers the credit not to pass link
weight, add `rel="nofollow"`; agree it with the client.

## Releasing a new version

1. Bump the version in `powered-by-garno.php` (header and `GARNO_BADGE_VERSION`),
   `powered-by-garno.js` (`VERSION`) and `package.json`.
   `bin/check-version.sh` checks they agree; CI runs it on every push.
2. Commit, then tag and push:

   ```bash
   git tag v1.0.1 && git push origin main v1.0.1
   ```

3. The release workflow checks the tag against the code, builds
   `powered-by-garno.zip` and publishes the GitHub release. Composer and npm
   installs pick up the tag directly.

## Demo

Open `demo.html` in a browser.
