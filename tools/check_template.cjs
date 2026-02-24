const fs = require('fs');
const path = process.argv[2] || 'resources/js/components/hrpanel.vue';
const content = fs.readFileSync(path, 'utf8');
const tmplMatch = content.match(/<template[^>]*>([\s\S]*?)<\/template>/i);
if (!tmplMatch) { console.error('No <template> found'); process.exit(2); }
const tmpl = tmplMatch[1];
const voidTags = new Set(['area','base','br','col','embed','hr','img','input','link','meta','param','source','track','wbr']);
const stack = [];
const re = /<(\/)?([a-zA-Z0-9-]+)([^>]*)>/gs;
let m;
while ((m = re.exec(tmpl)) !== null) {
  const isClose = !!m[1];
  const tag = m[2];
  const attrs = m[3] || '';
  const selfClose = /\/$/.test(attrs) || attrs.trim().endsWith('/');
  const upto = tmpl.slice(0, m.index);
  const lineNum = upto.split('\n').length;
  if (isClose) {
    if (stack.length===0) {
      console.error(`Unexpected closing </${tag}> at line ${lineNum}`);
      process.exit(3);
    }
    const top = stack[stack.length-1];
    if (top.tag === tag) {
      stack.pop();
    } else {
      console.error(`Mismatched closing </${tag}> at line ${lineNum}, expected </${top.tag}>`);
      process.exit(4);
    }
  } else {
    if (voidTags.has(tag) || selfClose) {
      // ignore
    } else {
      stack.push({tag, line: lineNum});
    }
  }
}

// look for stray '<' characters that are not starting a tag
for (let i = 0; i < tmpl.length; i++) {
  if (tmpl[i] === '<') {
    const next = tmpl[i+1] || '';
    if (!(/[a-zA-Z\/!\?]/).test(next)) {
      const upTo = tmpl.slice(0, i);
      const lineNum = upTo.split('\n').length;
      console.error(`Stray '<' at template line ${lineNum} (next char '${next}')`);
      process.exit(6);
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
