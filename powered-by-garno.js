/**
 * <garno-attribution> - the GARNO credit for client sites, for pages that
 * are not rendered by the WordPress plugin or the Laravel component.
 *
 * Optional. The attribution is plain HTML, so the tag's content is the
 * credit itself and works without this script:
 *
 *   <garno-attribution mode="built">Built by <a href="https://garno.com.au/">GARNO</a></garno-attribution>
 *
 * With the script loaded, the content is rendered from the attributes, so an
 * empty tag is enough:
 *
 *   <script src="/path/to/powered-by-garno.js" defer></script>
 *   <garno-attribution mode="maintained"></garno-attribution>
 *
 * Attributes (all optional):
 *   mode    "built" (default), "improved", "maintained" or "custom".
 *           "powered" still works but is deprecated.
 *   text    The wording for mode="custom". Its first "GARNO" becomes the
 *           link; without one, " GARNO" is appended.
 *   url     Where "GARNO" links. Defaults to https://garno.com.au/.
 *   target  "_blank" opens a new tab (and adds rel="noopener"); by default
 *           the link opens in the same tab.
 *   rel     Extra link relations, e.g. "nofollow".
 *   hidden  The standard attribute hides it.
 *
 * Light DOM, no styles: it inherits the footer's font and colours, and the
 * host's CSS can style `.garno-attribution` or the link like any other text.
 *
 * <powered-by-garno> is the deprecated 1.x name for the same element; its
 * `href` is read as `url`, and `theme` and `size` are ignored.
 */

// A classic script with no imports or exports, wrapped so nothing leaks into
// the host page's globals. That lets the same file load everywhere: a plain
// <script>, <script type="module">, or `import` from a bundler.
(() => {
  const VERSION = '2.0.0';
  const URL_DEFAULT = 'https://garno.com.au/';
  const NAME = 'GARNO';
  const MODES = {
    built: 'Built by',
    improved: 'Improved by',
    maintained: 'Maintained by',
    powered: 'Powered by', // Deprecated: GARNO is not the platform a site runs on.
  };

  const escape = (value) => String(value).replace(/[&<>"']/g, (c) => `&#${c.charCodeAt(0)};`);
  const tokens = (value, pattern) => [...new Set(String(value || '').trim().split(/\s+/).filter((t) => pattern.test(t)))];

  /** The text before and after the linked name. */
  function wording(mode, text) {
    mode = String(mode || '').trim().toLowerCase();
    text = String(text || '').replace(/\s+/g, ' ').trim();

    if (mode === 'custom' && text) {
      const at = text.indexOf(NAME);
      return at === -1 ? [`${text} `, ''] : [text.slice(0, at), text.slice(at + NAME.length)];
    }

    return [`${MODES[mode] || MODES.built} `, ''];
  }

  /** The attribution markup; mirrors Attribution::render() in PHP. */
  function html({ mode, text, url, show = true, className, target, rel } = {}) {
    if (show === false || /^(0|false|no|off|)$/i.test(String(show).trim())) return '';

    const [before, after] = wording(mode, text);
    const href = /^https?:\/\/[^\s"<>]+$/i.test(String(url || '').trim()) ? String(url).trim() : URL_DEFAULT;
    const blank = target === '_blank';

    const rels = tokens(String(rel || '').toLowerCase(), /^[a-z-]+$/);
    if (blank && !rels.includes('noopener')) rels.push('noopener');

    const classes = [...new Set(['garno-attribution', ...tokens(className, /^[A-Za-z0-9_-]+$/)])];

    const link = `<a href="${escape(href)}"`
      + (blank ? ' target="_blank"' : '')
      + (rels.length ? ` rel="${escape(rels.join(' '))}"` : '')
      + (blank ? ` aria-label="${NAME} (opens in a new tab)"` : '')
      + `>${NAME}</a>`;

    return `<span class="${escape(classes.join(' '))}">${escape(before)}${link}${escape(after)}</span>`;
  }

  class GarnoAttribution extends HTMLElement {
    static observedAttributes = ['mode', 'text', 'url', 'href', 'target', 'rel'];

    static version = VERSION;

    static html = html;

    connectedCallback() {
      this.render();
    }

    attributeChangedCallback() {
      if (this.isConnected) this.render();
    }

    render() {
      this.innerHTML = html({
        mode: this.getAttribute('mode'),
        text: this.getAttribute('text'),
        url: this.getAttribute('url') || this.getAttribute('href'),
        target: this.getAttribute('target'),
        rel: this.getAttribute('rel'),
      });
    }
  }

  // The deprecated 1.x tag. A separate subclass because one constructor
  // cannot be registered under two names.
  class PoweredByGarno extends GarnoAttribution {}

  if (!customElements.get('garno-attribution')) customElements.define('garno-attribution', GarnoAttribution);
  if (!customElements.get('powered-by-garno')) customElements.define('powered-by-garno', PoweredByGarno);
})();
