# Powered by Garno

The "Powered by Garno" credit for client sites: the Garno mark (the G with
the amber arrow) and the words *Powered by Garno*, as a small animated link.

It is a standard **web component** in one dependency-free file
(`powered-by-garno.js`, ~8 kB unminified, ~3 kB gzipped). Browsers understand custom elements
natively, so the same file works in WordPress, Laravel, Node/Express, Next.js,
Nuxt, React, Vue, Svelte or a static page without an adapter.

- **Isolated.** Shadow DOM: the client's CSS cannot break it, and its CSS
  cannot leak into the client's site.
- **CSP-friendly.** Styles go through a constructable stylesheet, which a
  strict `style-src` without `'unsafe-inline'` allows. No inline scripts.
- **No third-party requests.** No fonts, no tracking, no remote loading. The
  logo is inline SVG. Ship the file with the site; do not hot-link it from
  garno.com.au (that would add a third-party request to every client page).
- **Accessible.** One link, named "Powered by Garno (opens in a new tab)", with
  a visible focus ring. Animation never runs under `prefers-reduced-motion`.
- **Works without JavaScript.** The `<a>` you put inside the tag is shown if
  the script never runs.

## Markup

```html
<script type="module" src="/path/to/powered-by-garno.js"></script>

<powered-by-garno theme="dark">
  <a href="https://garno.com.au/">Powered by Garno</a>
</powered-by-garno>
```

| Attribute | Values | Default |
|-----------|--------|---------|
| `theme`   | `inherit` - takes the surrounding text colour, blends into any footer<br>`light` - brand colours, for light backgrounds<br>`dark` - for dark footers | `inherit` |
| `size`    | `sm` (12px text), `md` (14px) | `sm` |
| `href`    | Any URL | `https://garno.com.au/?utm_source=<client host>&utm_medium=powered-by-badge` |
| `target`  | `_blank`, `_self` | `_blank` |
| `rel`     | Extra relations, e.g. `nofollow` (`noopener` is added for a new tab) | none |

Animation: the arrow shoots through the G once when the badge first scrolls
into view, and nudges forward on hover and keyboard focus.

Styling from outside: the link is exposed as `::part(link)`, e.g.
`powered-by-garno::part(link) { padding: 8px; }`.

## WordPress

Copy this folder into `wp-content/plugins/powered-by-garno/` and activate
**Powered by Garno**. Then place it:

- Block or classic editor, footer widget: `[powered_by_garno theme="dark"]`
- Theme footer template: `<?php garno_badge(['theme' => 'dark']); ?>`
- Sage/Blade: `@php(garno_badge(['theme' => 'dark']))`

The script is only enqueued on pages that render a badge.

## Laravel

Copy `powered-by-garno.js` to `public/vendor/garno/`, then in the layout:

```blade
<script type="module" src="{{ asset('vendor/garno/powered-by-garno.js') }}"></script>
...
<footer>
  <powered-by-garno theme="dark"><a href="https://garno.com.au/">Powered by Garno</a></powered-by-garno>
</footer>
```

With Vite, keep the copy in `resources/js/vendor/` and import it from
`resources/js/app.js` instead: `import './vendor/powered-by-garno.js';`

## Node, React, Vue, Next.js

Install from the file until the package is published:

```bash
npm install ../path/to/packages/powered-by-garno
```

```js
import '@garno/powered-by';
```

Then use the tag in any template:

```jsx
// React 19+ passes attributes to custom elements directly.
<powered-by-garno theme="dark">
  <a href="https://garno.com.au/">Powered by Garno</a>
</powered-by-garno>
```

Next.js / Nuxt: import it in a client-only component (`'use client'` or
`<ClientOnly>`), because `customElements` exists only in the browser. The
server still renders the fallback link.

Express or plain HTML: serve the file statically and use the `<script>` tag.

## Links and SEO

The badge is a branded link ("Garno"), not keyword anchor text, which is what
search engines expect from a site credit. If a client prefers the credit not
to pass link weight, add `rel="nofollow"` - agree it with the client rather
than deciding for them.

## Demo

```bash
python3 -m http.server 8765
```

Open http://127.0.0.1:8765/demo.html. (Module scripts do not load from
`file://`, so the demo needs a local server.)
