/**
 * Copies Material Symbols woff2 into public/fonts so icons load from the app origin
 * (dev, build, and serve) without depending on Vite resolving node_modules URLs.
 */
import { copyFileSync, existsSync, mkdirSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const source = join(root, 'node_modules', '@material-symbols', 'font-400', 'material-symbols-outlined.woff2');
const targetDir = join(root, 'public', 'fonts');
const target = join(targetDir, 'material-symbols-outlined.woff2');

if (! existsSync(source)) {
    console.error('Material Symbols font not found. Run: npm install');
    process.exit(1);
}

if (! existsSync(targetDir)) {
    mkdirSync(targetDir, { recursive: true });
}

copyFileSync(source, target);
console.log('Copied Material Symbols font to public/fonts/material-symbols-outlined.woff2');
