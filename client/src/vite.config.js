import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import autoprefixer from "autoprefixer";

export default defineConfig(({ command, mode, ssrBuild }) => {

  return {

    resolve: {
        alias: {}
    },

    build: {
      chunkSizeWarningLimit: 1500,
      emptyOutDir: true,
      outDir: '../dist',
      rollupOptions: {
        output: {
          entryFileNames: `[name].js`,
          chunkFileNames: `js/[name]-[hash].js`,
          assetFileNames: (assetInfo) => {
            if (assetInfo.name.endsWith('.css')) {
              return '[name][extname]'
            } else if (
              assetInfo.name.match(/(\.(woff2?|eot|ttf|otf)|font\.svg)(\?.*)?$/)
            ) {
              return 'fonts/[name][extname]'
            } else if (assetInfo.name.match(/\.(jpg|png|svg)$/)) {
              return 'images/[name][extname]'
            }

            return 'js/[name][extname]'
          },
        }
      }
    },

    plugins: [

      laravel({
        input: [
          'src/icon-styles.scss',
          'src/icon.js',
        ],
        refresh: true,
      }),

    ],

    css: {

      postcss: {
        plugins: [
          autoprefixer,
        ],
      }
    },
  }

});
