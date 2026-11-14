const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  devServer: {
    proxy: {
      '/projecthub': {
        target: 'http://localhost',
        changeOrigin: true,
        secure: false
      }
    }
  }
})
