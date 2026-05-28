/**
 * Runs Vite dev server and removes public/hot on exit so Laravel does not
 * keep pointing at a dead :5173 server after Ctrl+C.
 */
import { spawn } from 'node:child_process';
import { existsSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const hotPath = join(root, 'public', 'hot');
const viteBin = join(root, 'node_modules', 'vite', 'bin', 'vite.js');
const devOrigin = process.env.VITE_DEV_SERVER_URL || 'http://127.0.0.1:5173';

function removeHotFile() {
    if (existsSync(hotPath)) {
        unlinkSync(hotPath);
        process.stdout.write('Removed public/hot — Laravel will load assets from public/build.\n');
    }
}

function writeHotFile() {
    writeFileSync(hotPath, `${devOrigin.replace(/\/$/, '')}/`, 'utf8');
    process.stdout.write(`Wrote public/hot → ${devOrigin}\n`);
}

let shuttingDown = false;

function shutdown(exitCode = 0) {
    if (shuttingDown) {
        return;
    }
    shuttingDown = true;
    removeHotFile();
    process.exit(exitCode);
}

const child = spawn(process.execPath, [viteBin], {
    cwd: root,
    stdio: 'inherit',
    env: process.env,
});

process.on('SIGINT', () => {
    child.kill('SIGINT');
});

process.on('SIGTERM', () => {
    child.kill('SIGTERM');
});

child.on('spawn', () => {
    setTimeout(writeHotFile, 800);
});

child.on('exit', (code, signal) => {
    removeHotFile();
    if (signal) {
        process.kill(process.pid, signal);
        return;
    }
    process.exit(code ?? 0);
});

process.stdout.write(
    '\nVite dev: open the app at http://127.0.0.1:8000 (same host as APP_URL).\n'
    + 'Run in another terminal: php artisan serve --host=127.0.0.1 --port=8000\n'
    + 'Or use: npm run local\n\n',
);
