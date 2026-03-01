const fs = require('fs');
const es = JSON.parse(fs.readFileSync('./src/i18n/locales/es-AR.json','utf8'));
const en = JSON.parse(fs.readFileSync('./src/i18n/locales/en-US.json','utf8'));
function keys(o,p){p=p||'';return Object.keys(o).flatMap(function(k){const f=p?p+'.'+k:k;return(typeof o[k]==='object'&&o[k]!==null)?keys(o[k],f):[f];});}
const enK=new Set(keys(en));
const esK=new Set(keys(es));
const miss=Array.from(enK).filter(function(k){return !esK.has(k);});
const extra=Array.from(esK).filter(function(k){return !enK.has(k);});
console.log('EN keys:',enK.size,'ES keys:',esK.size);
console.log('Missing in ES ('+miss.length+'):');
miss.sort().forEach(function(k){console.log('  '+k);});
console.log('Extra in ES ('+extra.length+'):');
extra.sort().slice(0,10).forEach(function(k){console.log('  '+k);});
