/**
 * Removes public/hot so @vite uses the manifest (public/build) instead of a dead dev server.
 */
import { existsSync, unlinkSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const hotPath = join(root, 'public', 'hot');

if (existsSync(hotPath)) {
    unlinkSync(hotPath);
    process.stdout.write('Removed public/hot — Laravel will load assets from public/build.\n');
}
