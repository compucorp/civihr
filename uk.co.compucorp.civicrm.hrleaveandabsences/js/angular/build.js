({
  baseUrl: 'src',
  dir: 'dist',
  wrapShim: true,
  modules: [
    {name: 'my-leave'},
    {name: 'manager-leave'},
    {name: 'absence-tab'}
  ],
  mainConfigFile: 'src/leave-absences/shared/config.js',
  paths: {
    'common': 'empty:'
  },
  findNestedDependencies: true
});
