module.exports = {
  pages: {
    index: {
      title: 'Flip',
      entry: 'src/main.js',
      template: 'public/index.html',
      filename: 'index.html',
    }
  },
  transpileDependencies: [
    'vuetify'
  ]
}
