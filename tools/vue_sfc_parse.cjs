const fs = require('fs');
const sfc = require('@vue/compiler-sfc');
const content = fs.readFileSync('resources/js/components/hrpanel.vue','utf8');
let res;
try {
  res = sfc.parse(content, { filename: 'hrpanel.vue' });
  console.log('descriptor keys:', Object.keys(res.descriptor));
  if (res.errors && res.errors.length) {
    console.error('errors:');
    res.errors.forEach((e, idx) => {
      console.error('--- error', idx, '---');
      console.error('message:', e.message);
      console.error('loc:');
      console.dir(e.loc, { depth: null });
    });
    process.exit(2);
  }
  console.log('SFC parsed OK');
} catch (e) {
  console.error('parse threw:', e && e.message);
  if (e && e.loc) console.error('loc:', e.loc);
  process.exit(3);
}
