const fs = require('fs');
const compiler = require('@vue/compiler-dom');
const content = fs.readFileSync('resources/js/components/hrpanel.vue','utf8');
const m = content.match(/<template[^>]*>([\s\S]*?)<\/template>/i);
if (!m) { console.error('no template'); process.exit(1); }
const tmpl = m[1];
try {
  const ast = compiler.parse(tmpl, { sourceMap: true });
  console.log('parse ok');
} catch (e) {
  console.error('parse error:', e.message);
  console.error(e);
  process.exit(2);
}
