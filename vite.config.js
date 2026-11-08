import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
export default defineConfig({
    server: process.env.VITE_HMR_HOST
        ? {
              host: "0.0.0.0",
              port: 5173,
              strictPort: true,
              hmr: {
                  host: process.env.VITE_HMR_HOST,
                  clientPort: Number(process.env.VITE_HMR_PORT || 5173),
              },
              watch: {
                  usePolling: process.env.VITE_USE_POLLING === "true",
              },
          }
        : undefined,
    plugins: [
        laravel({ input: ["resources/js/app.js"], refresh: true }),
        vue({ template: { transformAssetUrls: false } }),
    ],
});
