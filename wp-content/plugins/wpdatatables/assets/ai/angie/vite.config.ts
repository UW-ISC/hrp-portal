import { defineConfig } from 'vite'
import { fileURLToPath } from 'node:url'

export default defineConfig({
  build: {
    emptyOutDir: false,
    outDir: fileURLToPath(new URL('../../js/angie', import.meta.url)),
    lib: {
      entry: fileURLToPath(new URL('./src/wpdatatables-mcp-server.ts', import.meta.url)),
      formats: ['es'],
      fileName: () => 'wpdatatables-angie.js',
    },
    rollupOptions: {
      output: {
        inlineDynamicImports: true,
      },
    },
    target: 'es2022',
    minify: true,
    sourcemap: false,
  },
})
