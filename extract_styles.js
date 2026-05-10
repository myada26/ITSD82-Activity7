import fs from 'fs';
import path from 'path';

const files = [
  'Transaction history.html',
  'New transaction _ step 2.html',
  'Receipt detail.html',
  'Sign in.html'
];

const basePath = 'c:\\Users\\carlos\\Desktop\\FACS 2\\facs2';

files.forEach(file => {
  const filePath = path.join(basePath, file);
  if (!fs.existsSync(filePath)) {
    console.log(`File not found: ${file}`);
    return;
  }
  
  const content = fs.readFileSync(filePath, 'utf8');
  
  // Extract all classes
  const classMatches = [...content.matchAll(/class="([^"]+)"/g)];
  let allClasses = [];
  classMatches.forEach(m => {
    allClasses.push(...m[1].split(/\s+/).filter(Boolean));
  });
  
  const uniqueClasses = [...new Set(allClasses)].sort();
  
  // Group Tailwind classes by prefix
  const bgClasses = uniqueClasses.filter(c => c.startsWith('bg-'));
  const textClasses = uniqueClasses.filter(c => c.startsWith('text-'));
  const borderClasses = uniqueClasses.filter(c => c.startsWith('border-') || c === 'border');
  const roundedClasses = uniqueClasses.filter(c => c.startsWith('rounded-') || c === 'rounded');
  const shadowClasses = uniqueClasses.filter(c => c.startsWith('shadow-') || c === 'shadow');
  const spacingClasses = uniqueClasses.filter(c => /^[pm][xytrbl]?-/.test(c) || /^gap-/.test(c));
  
  console.log(`\n--- Analysis for ${file} ---`);
  console.log(`Backgrounds:`, bgClasses.join(', '));
  console.log(`Text Colors/Sizes:`, textClasses.join(', '));
  console.log(`Borders:`, borderClasses.join(', '));
  console.log(`Border Radius:`, roundedClasses.join(', '));
  console.log(`Shadows:`, shadowClasses.join(', '));
  // Not printing spacing classes because there could be too many, but good to know
});
