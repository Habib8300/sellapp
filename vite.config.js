import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const codespacesHost = process.env.CODESPACE_NAME
    && process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
    ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`
    : null;

export default defineConfig({
    server: {
        host: '0.0.0.0',
        ...(codespacesHost
            ? {
                origin: `https://${codespacesHost}`,
                hmr: {
                    host: codespacesHost,
                    protocol: 'wss',
                    clientPort: 443,
                },
                allowedHosts: [codespacesHost],
            }
            : {}),
    },
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
