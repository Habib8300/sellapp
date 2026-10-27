import { spawn } from 'node:child_process';
import { fileURLToPath } from 'node:url';

const viteCli = fileURLToPath(new URL('../node_modules/vite/bin/vite.js', import.meta.url));
const args = process.env.CODESPACES === 'true' ? ['build', '--watch'] : [];
const vite = spawn(process.execPath, [viteCli, ...args], { stdio: 'inherit' });

process.once('SIGINT', () => vite.kill('SIGINT'));
process.once('SIGTERM', () => vite.kill('SIGTERM'));

vite.once('exit', (code, signal) => {
    process.exitCode = code ?? (signal ? 1 : 0);
});
