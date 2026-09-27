/**
 * <powered-by-garno> - the "Powered by Garno" credit for client sites.
 *
 * One dependency-free file that works anywhere HTML does: WordPress, Laravel,
 * Node, React, Vue, or a static page. It is a standard custom element, so the
 * host framework needs no adapter.
 *
 *   <script type="module" src="/path/to/powered-by-garno.js"></script>
 *   <powered-by-garno theme="dark">
 *     <a href="https://garno.com.au/">Powered by Garno</a>
 *   </powered-by-garno>
 *
 * The <a> inside is the no-JavaScript fallback: it is what shows if the script
 * never runs, and it is replaced by the badge when it does.
 *
 * Attributes (all optional):
 *   theme   "inherit" (default: takes the surrounding text colour, so it sits
 *           quietly in any footer), "light" (brand colours on a light
 *           background) or "dark" (for dark footers).
 *   size    "sm" (default, 12px text) or "md" (14px).
 *   href    Where the badge links. Defaults to the Garno site, tagged with
 *           the host name so referrals can be told apart.
 *   target  "_blank" (default) or "_self".
 *   rel     Extra link relations, e.g. "nofollow". "noopener" is always
 *           added for a new tab.
 *
 * Built to be a good guest on someone else's site:
 *   - Shadow DOM, so the host's CSS cannot break it and its CSS cannot leak.
 *   - Styles via a constructable stylesheet, which strict CSPs without
 *     'unsafe-inline' allow; a <style> fallback covers old browsers.
 *   - No network requests, no fonts, no tracking. The logo is inline SVG.
 *   - Animation plays once when the badge scrolls into view and on hover or
 *     focus; never under prefers-reduced-motion.
 *
 * Source: packages/powered-by-garno in the Garno repository.
 */

const VERSION = '1.0.0';
const HOME = 'https://garno.com.au/';

// The G and the arrow from the Garno logo, cropped to the mark alone.
const MARK = `
<svg class="mark" viewBox="176 146 432 408" aria-hidden="true" focusable="false">
  <defs>
    <mask id="knock" maskUnits="userSpaceOnUse" x="176" y="146" width="432" height="408">
      <rect x="176" y="146" width="432" height="408" fill="#fff"/>
      <polygon points="190,327 408,327 365,270 433,270 498,352 433,434 365,434 408,382 190,382"
        fill="#000" stroke="#000" stroke-width="36" stroke-linejoin="round"/>
    </mask>
  </defs>
  <g mask="url(#knock)">
    <path class="ring" d="M550 350 A160 160 0 1 1 535 282.4" fill="none" stroke="currentColor" stroke-width="78"/>
    <rect class="bar" x="506" y="326" width="90" height="72" fill="currentColor"/>
  </g>
  <polygon class="arrow" points="190,327 408,327 365,270 433,270 498,352 433,434 365,434 408,382 190,382"/>
</svg>`;

const CSS = `
:host {
  display: inline-block;
  vertical-align: middle;
  line-height: 1;
  --pbg-amber: #fdb813;
  --pbg-mark: currentColor;
  --pbg-text: currentColor;
  --pbg-muted: currentColor;
  --pbg-muted-opacity: 0.72;
  --pbg-size: 12px;
}
:host([hidden]) { display: none; }
:host([size="md"]) { --pbg-size: 14px; }
:host([theme="light"]) {
  --pbg-mark: #0444b4;
  --pbg-text: #14171a;
  --pbg-muted: #5b6168;
  --pbg-muted-opacity: 1;
}
:host([theme="dark"]) {
  --pbg-mark: #ffffff;
  --pbg-text: #f1efe9;
  --pbg-muted: #a3a9b1;
  --pbg-muted-opacity: 1;
}

a {
  display: inline-flex;
  align-items: center;
  gap: 0.5em;
  min-height: 24px;
  padding: 2px 4px;
  border-radius: 6px;
  font: 500 var(--pbg-size)/1 system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
  letter-spacing: 0.01em;
  color: var(--pbg-text);
  text-decoration: none;
  -webkit-tap-highlight-color: transparent;
}
a:focus-visible {
  outline: 2px solid currentColor;
  outline-offset: 2px;
}

.mark {
  width: 1.6em;
  height: 1.5em;
  flex: none;
  overflow: visible;
  color: var(--pbg-mark);
}
.arrow { fill: var(--pbg-amber); }

.by {
  color: var(--pbg-muted);
  opacity: var(--pbg-muted-opacity);
}
.name {
  font-weight: 700;
  letter-spacing: -0.01em;
}

@media (prefers-reduced-motion: no-preference) {
  /* Entrance, once, when the badge first scrolls into view. */
  :host([data-state="enter"]) .arrow {
    animation: pbg-shoot 700ms cubic-bezier(0.2, 0.8, 0.2, 1) both;
  }
  :host([data-state="enter"]) .ring {
    stroke-dasharray: 960;
    animation: pbg-draw 650ms cubic-bezier(0.65, 0, 0.25, 1) 250ms both;
  }
  :host([data-state="enter"]) .text {
    animation: pbg-fade 500ms cubic-bezier(0.2, 0.8, 0.2, 1) 350ms both;
  }

  /* Hover and focus: the arrow nudges through the G and back. */
  .arrow {
    transition: transform 380ms cubic-bezier(0.2, 0.8, 0.2, 1);
  }
  a:hover .arrow,
  a:focus-visible .arrow {
    transform: translateX(90px); /* SVG units: ~4px at 12px text */
  }
  a:hover .name,
  a:focus-visible .name {
    text-decoration: underline;
    text-underline-offset: 0.2em;
  }
}

@keyframes pbg-shoot {
  0% { transform: translateX(-340px); opacity: 0; }
  30% { opacity: 1; }
  80% { transform: translateX(24px); }
  100% { transform: translateX(0); }
}
@keyframes pbg-draw {
  from { stroke-dashoffset: 960; }
  to { stroke-dashoffset: 0; }
}
@keyframes pbg-fade {
  from { opacity: 0; transform: translateX(-4px); }
  to { opacity: 1; transform: none; }
}
`;

let sheet = null;
function styles() {
  if (sheet === null) {
    try {
      sheet = new CSSStyleSheet();
      sheet.replaceSync(CSS);
    } catch {
      sheet = false; // No constructable stylesheets: fall back to <style>.
    }
  }
  return sheet;
}

function defaultHref() {
  try {
    const url = new URL(HOME);
    url.searchParams.set('utm_source', window.location.hostname || 'badge');
    url.searchParams.set('utm_medium', 'powered-by-badge');
    return url.toString();
  } catch {
    return HOME;
  }
}

class PoweredByGarno extends HTMLElement {
  static observedAttributes = ['href', 'target', 'rel'];

  connectedCallback() {
    if (!this.shadowRoot) {
      this.render();
      this.observe();
    }
  }

  disconnectedCallback() {
    this.observer?.disconnect();
  }

  attributeChangedCallback() {
    if (this.shadowRoot) this.updateLink();
  }

  render() {
    const root = this.attachShadow({ mode: 'open' });
    const sheet = styles();

    if (sheet) {
      root.adoptedStyleSheets = [sheet];
    } else {
      const style = document.createElement('style');
      style.textContent = CSS;
      root.append(style);
    }

    const link = document.createElement('a');
    link.setAttribute('part', 'link');
    link.innerHTML = `${MARK}<span class="text"><span class="by">Powered by</span> <span class="name">Garno</span></span>`;
    root.append(link);

    this.link = link;
    this.updateLink();
  }

  updateLink() {
    const target = this.getAttribute('target') === '_self' ? '_self' : '_blank';
    this.link.href = this.getAttribute('href') || defaultHref();
    this.link.target = target;

    const rel = new Set((this.getAttribute('rel') || '').split(/\s+/).filter(Boolean));
    if (target === '_blank') rel.add('noopener');

    if (rel.size) {
      this.link.rel = [...rel].join(' ');
    } else {
      this.link.removeAttribute('rel');
    }

    this.link.setAttribute(
      'aria-label',
      target === '_blank' ? 'Powered by Garno (opens in a new tab)' : 'Powered by Garno',
    );
  }

  // Play the entrance once, the first time the badge is actually seen.
  observe() {
    if (!('IntersectionObserver' in window)) return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    this.observer = new IntersectionObserver((entries) => {
      if (!entries.some((entry) => entry.isIntersecting)) return;
      this.observer.disconnect();
      this.dataset.state = 'enter';
    }, { threshold: 0.6 });

    this.observer.observe(this);
  }
}

if (!customElements.get('powered-by-garno')) {
  customElements.define('powered-by-garno', PoweredByGarno);
}

export { PoweredByGarno, VERSION };
