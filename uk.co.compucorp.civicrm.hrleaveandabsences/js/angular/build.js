({
  baseUrl: 'src',
  dir: 'dist',
  wrapShim: true,
  modules: [
    {name: 'my-leave'},
    {name: 'manager-leave'}
  ],
  mainConfigFile: 'src/leave-absences/shared/config.js',
  paths: {
    'common': 'empty:'
  }
})
