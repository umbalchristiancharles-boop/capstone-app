const fs = require('fs');
const { parse } = require('@vue/compiler-sfc');
const content = fs.readFileSync('resources/js/components/hrpanel.vue','utf8');
try {
  const desc = parse(content, { filename: 'hrpanel.vue' });
  console.log('descriptor blocks:', Object.keys(desc.descriptor).join(', '));
  console.log('template block exists:', !!desc.descriptor.template);
  if (desc.descriptor.template) {
    console.log('template content length:', desc.descriptor.template.content.length);
  }
} catch (e) {
  console.error('SFC parse error:');
  console.error(e.message);
  console.error(e);
  process.exit(2);
}
