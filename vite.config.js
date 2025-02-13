import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    server: {
        host: true,        // Allows connections from outside the container 
        strictPort: true,   // Ensures Vite uses exactly the specified port
        port: 5173          // Matches your Docker port mapping
      }
});
