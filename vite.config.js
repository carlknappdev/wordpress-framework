import { defineConfig } from "vite";
import { ViteImageOptimizer } from "vite-plugin-image-optimizer";
import eslint from "vite-plugin-eslint";
import stylelint from "vite-plugin-stylelint";

export default defineConfig({
  css: {
    preprocessorOptions: {
      scss: {
        api: "modern-compiler",
      },
    },
  },
  base: "/wp-content/themes/theme/dist",
  server: {
    port: 5173,
    host: "0.0.0.0",
    origin: "http://localhost:5173",
  },
  build: {
    manifest: true,
    rollupOptions: {
      input: {
        index: "./assets/index.js",
        login: "./assets/login.js",
      },
    },
    outDir: "./public/app/themes/theme/dist",
    copyPublicDir: false,
    assetsDir: "assets",
    cssCodeSplit: true,
    sourcemap: true,
  },
  plugins: [ViteImageOptimizer({}), eslint(), stylelint()],
});
