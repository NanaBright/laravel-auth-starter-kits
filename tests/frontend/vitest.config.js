import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

export default defineConfig({
    plugins: [vue()],
    resolve: {
        alias: {
            '@': resolve(__dirname, '.'),
        },
    },
    test: {
        globals: true,
        environment: 'happy-dom',
        setupFiles: ['./setup.js'],
        include: ['**/*.test.js'],
        exclude: ['node_modules', 'dist'],
        coverage: {
            provider: 'v8',
            reporter: ['text', 'json', 'html'],
            exclude: [
                'node_modules/',
                'setup.js',
                'utils/',
                '**/*.test.js',
            ],
        },
        // Reporter configuration
        reporters: ['verbose'],
        // Test timeout
        testTimeout: 10000,
        // Mock clearance
        clearMocks: true,
        // Restore mocks automatically
        restoreMocks: true,
    },
})
