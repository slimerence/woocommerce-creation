const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  filenameHashing: false,
  publicPath: process.env.NODE_ENV === 'production'
    ? '/wp-content/plugins/kongfuseo-addon/app/dist'
    : '/',
})
