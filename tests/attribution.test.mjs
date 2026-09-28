// Tests for the <garno-attribution> element with a minimal DOM stub.
//
//   node --test tests/

import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../powered-by-garno.js', import.meta.url), 'utf8');

function load() {
  const registry = new Map();
  class HTMLElement {
    constructor() {
      this.attributes = new Map();
      this.innerHTML = '';
      this.isConnected = false;
    }
    getAttribute(name) {
      return this.attributes.has(name) ? this.attributes.get(name) : null;
    }
    setAttribute(name, value) {
      this.attributes.set(name, String(value));
      if (this.constructor.observedAttributes?.includes(name)) this.attributeChangedCallback?.();
    }
  }
  const customElements = {
    get: (name) => registry.get(name),
    define: (name, ctor) => registry.set(name, ctor),
  };
  vm.runInNewContext(source, { HTMLElement, customElements });
  return registry;
}

const elements = load();
const Element = elements.get('garno-attribution');
const { html } = Element;

const link = '<a href="https://garno.com.au/">GARNO</a>';
const wrap = (inner, cls = 'garno-attribution') => `<span class="${cls}">${inner}</span>`;

function mount(name, attributes = {}) {
  const el = new (elements.get(name))();
  for (const [key, value] of Object.entries(attributes)) el.attributes.set(key, value);
  el.isConnected = true;
  el.connectedCallback();
  return el;
}

test('default mode is Built by GARNO', () => {
  assert.equal(html(), wrap(`Built by ${link}`));
  assert.equal(mount('garno-attribution').innerHTML, wrap(`Built by ${link}`));
});

test('predefined modes', () => {
  assert.equal(html({ mode: 'built' }), wrap(`Built by ${link}`));
  assert.equal(html({ mode: 'improved' }), wrap(`Improved by ${link}`));
  assert.equal(html({ mode: 'maintained' }), wrap(`Maintained by ${link}`));
  assert.equal(html({ mode: 'hosted' }), wrap(`Built by ${link}`), 'unknown falls back to built');
  assert.equal(html({ mode: 'powered' }), wrap(`Powered by ${link}`), 'deprecated mode still works');
});

test('custom text', () => {
  assert.equal(html({ mode: 'custom', text: 'Website support by' }), wrap(`Website support by ${link}`));
  assert.equal(html({ mode: 'custom', text: 'Designed with GARNO in Perth' }), wrap(`Designed with ${link} in Perth`));
  assert.equal(html({ mode: 'custom', text: ' ' }), wrap(`Built by ${link}`));
  assert.equal(html({ mode: 'improved', text: 'Other' }), wrap(`Improved by ${link}`));
});

test('custom text is escaped', () => {
  assert.equal(
    html({ mode: 'custom', text: '<img src=x onerror="alert(1)"> & by' }),
    wrap(`&#60;img src=x onerror=&#34;alert(1)&#34;&#62; &#38; by ${link}`),
  );
});

test('configurable url, unsafe urls rejected', () => {
  assert.equal(html({ url: 'https://garno.example/?a=1&b=2' }), wrap('Built by <a href="https://garno.example/?a=1&#38;b=2">GARNO</a>'));
  assert.equal(html({ url: 'javascript:alert(1)' }), wrap(`Built by ${link}`));
  assert.equal(html({ url: 'https://x.test/"onmouseover="x' }), wrap(`Built by ${link}`));
});

test('hidden', () => {
  assert.equal(html({ show: false }), '');
  assert.equal(html({ show: 'false' }), '');
  assert.equal(html({ show: true }), wrap(`Built by ${link}`));
});

test('link attributes', () => {
  assert.equal(
    html({ target: '_blank' }),
    wrap('Built by <a href="https://garno.com.au/" target="_blank" rel="noopener" aria-label="GARNO (opens in a new tab)">GARNO</a>'),
  );
  assert.equal(html({ target: '_top' }), wrap(`Built by ${link}`));
  assert.equal(html({ rel: 'NoFollow' }), wrap('Built by <a href="https://garno.com.au/" rel="nofollow">GARNO</a>'));
  assert.equal(html({ rel: '"><script>' }), wrap(`Built by ${link}`));
  assert.equal(html({ className: 'credit "><x' }), wrap(`Built by ${link}`, 'garno-attribution credit'));
});

test('element reads attributes and re-renders on change', () => {
  const el = mount('garno-attribution', { mode: 'custom', text: 'Support by', url: 'https://garno.example/' });
  assert.equal(el.innerHTML, wrap('Support by <a href="https://garno.example/">GARNO</a>'));
  el.setAttribute('mode', 'maintained');
  assert.equal(el.innerHTML, wrap('Maintained by <a href="https://garno.example/">GARNO</a>'));
});

test('deprecated <powered-by-garno> still works', () => {
  const el = mount('powered-by-garno', { theme: 'dark', size: 'md', href: 'https://garno.example/' });
  assert.equal(el.innerHTML, wrap('Built by <a href="https://garno.example/">GARNO</a>'));
  assert.equal(mount('powered-by-garno', { mode: 'powered' }).innerHTML, wrap(`Powered by ${link}`));
});

test('matches the PHP renderer', async () => {
  const { execFileSync } = await import('node:child_process');
  const cases = [
    {},
    { mode: 'maintained', target: '_blank', rel: 'nofollow', class: 'x' },
    { mode: 'custom', text: 'A <b> & "GARNO" \'site\'', url: 'https://garno.example/?a=1&b=2' },
  ];
  const php = `require 'src/Attribution.php'; foreach (json_decode($argv[1], true) as $o) echo html_entity_decode(Garno\\PoweredBy\\Attribution::render($o), ENT_QUOTES | ENT_HTML5), "\\n";`;
  let out;
  try {
    out = execFileSync('php', ['-r', php, JSON.stringify(cases)], { cwd: new URL('..', import.meta.url), encoding: 'utf8' });
  } catch (error) {
    if (error.code === 'ENOENT') return; // No PHP here; the PHP suite covers it.
    throw error;
  }
  const decode = (s) => s.replace(/&#(\d+);/g, (_, n) => String.fromCharCode(n)).replace(/&amp;/g, '&');
  const expected = cases.map(({ class: className, ...o }) => decode(html({ ...o, className })));
  assert.deepEqual(out.trimEnd().split('\n'), expected);
});
