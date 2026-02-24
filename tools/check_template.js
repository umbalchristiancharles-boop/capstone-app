const fs = require('fs');
const path = process.argv[2] || 'resources/js/components/hrpanel.vue';
const content = fs.readFileSync(path, 'utf8');
const tmplMatch = content.match(/<template[^>]*>([\s\S]*?)<\/template>/i);
if (!tmplMatch) { console.error('No <template> found'); process.exit(2); }
const tmpl = tmplMatch[1];
const lines = tmpl.split('\n');
const voidTags = new Set(['area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr']);
const stack = [];
for (let i=0;i<lines.length;i++){
  const line = lines[i];
  // find tags in order
  const re = /<(\/)?([a-zA-Z0-9-]+)([^>]*)>/g;
  let m;
  while ((m = re.exec(line)) !== null) {
    const isClose = !!m[1];
    const tag = m[2];
    const attrs = m[3] || '';
    const selfClose = /\/$/.test(attrs) || /\/>$/.test(m[0]) || attrs.trim().endsWith('/');
    if (isClose) {
      if (stack.length===0) {
        console.error(`Unexpected closing </${tag}> at line ${i+1}`);
        process.exit(3);
      }
      const top = stack[stack.length-1];
      if (top.tag === tag) {
        stack.pop();
      } else {
        console.error(`Mismatched closing </${tag}> at line ${i+1}, expected </${top.tag}>`);
        process.exit(4);
      }
    } else {
      if (voidTags.has(tag) || selfClose) {
        // ignore
      } else {
        stack.push({tag, line: i+1});
      }
    }
  }
}
if (stack.length) {
  console.error('Unclosed tag(s) found:');
  stack.forEach(s => console.error(`${s.tag} opened at line ${s.line}`));
  process.exit(5);
}
console.log('Template tags appear balanced.');
process.exit(0);
