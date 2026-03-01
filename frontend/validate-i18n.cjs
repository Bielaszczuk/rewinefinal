const es = require('./src/i18n/locales/es-AR.json');
const en = require('./src/i18n/locales/en-US.json');

function keys(o, p) {
  p = p || '';
  return Object.keys(o).flatMap(function(k) {
    const f = p ? p + '.' + k : k;
    return (typeof o[k] === 'object' && o[k] !== null) ? keys(o[k], f) : [f];
  });
}

const enK = new Set(keys(en));
const esK = new Set(keys(es));
const missing = Array.from(enK).filter(function(k) { return !esK.has(k); });
const extra   = Array.from(esK).filter(function(k) { return !enK.has(k); });

console.log('JSON valid');
console.log('Top-level ES keys: ' + Object.keys(es).join(', '));
console.log('Total EN keys: ' + enK.size + ' | Total ES keys: ' + esK.size);

if (missing.length) {
  console.log('MISSING in ES (' + missing.length + '):');
  missing.sort().forEach(function(k) { console.log('  ' + k); });
} else {
  console.log('All EN keys present in ES');
}

if (extra.length) {
  console.log('Extra in ES (' + extra.length + '):');
  extra.sort().forEach(function(k) { console.log('  ' + k); });
} else {
  console.log('No extra keys in ES');
}
