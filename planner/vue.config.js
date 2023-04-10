const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  css: {
    extract: true
  },
  devServer: {
    proxy: {
      '/wp-json': {
        target: 'https://ourcreations.com.au/',
        changeOrigin: true,
        secrue: false,
      },
    }
  },
  publicPath: process.env.NODE_ENV === 'production'
    ? '/wp-content/plugins/kongfuseo-addon/planner/dist'
    : '/wp-content/plugins/kongfuseo-addon/planner/dist',
  transpileDependencies: true,
  filenameHashing: false,
})