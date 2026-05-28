/**
 * Local dev: Laravel (8000) + Vite (5173) in one command.
 * Production/Docker uses compiled assets in public/build instead.
 */
import { spawn } from 'node:child_process';
import { existsSync, unlinkSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join(dirname(fileURLToPath(import.meta.url)), '..');
const hotPath = join(root, 'public', 'hot');
const viteDevScript = join(root, 'scripts', 'vite-dev.mjs');
const devOrigin = process.env.VITE_DEV_SERVER_URL || 'http://127.0.0.1:5173';

function removeHotFile() {
    if (existsSync(hotPath)) {
        unlinkSync(hotPath);
    }
}

function spawnProcess(label, command, args) {
    const child = spawn(command, args, {
        cwd: root,
        stdio: 'inherit',
        env: process.env,
        shell: process.platform === 'win32',
    });

    child.on('exit', (code, signal) => {
        if (signal) {
            console.error(`[${label}] stopped (${signal})`);
        } else if (code !== 0) {
            console.error(`[${label}] exited with code ${code}`);
        }
        shutdown(code ?? 0);
    });

    return child;
}

let shuttingDown = false;
const children = [];

function shutdown(exitCode = 0) {
    if (shuttingDown) {
        return;
    }
    shuttingDown = true;

    for (const child of children) {
        if (!child.killed) {
            child.kill('SIGTERM');
        }
    }

    removeHotFile();
    setTimeout(() => process.exit(exitCode), 100);
}

process.on('SIGINT', () => shutdown(0));
process.on('SIGTERM', () => shutdown(0));

console.log(`Starting local stack: Vite ${devOrigin} + Laravel http://127.0.0.1:8000`);
console.log('Open http://127.0.0.1:8000 in your browser (not localhost).\n');

children.push(spawnProcess('vite', process.execPath, [viteDevScript]));
children.push(spawnProcess('laravel', 'php', ['artisan', 'serve', '--host=127.0.0.1', '--port=8000']));
