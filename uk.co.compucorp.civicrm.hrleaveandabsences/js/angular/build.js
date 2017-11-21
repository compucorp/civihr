({
  baseUrl: 'src',
  dir: 'dist',
  wrapShim: true,
  modules: [
    {name: 'admin-dashboard'},
    {name: 'absence-tab'},
    {name: 'manager-leave'},
    {name: 'manager-notification-badge'},
    {name: 'my-leave'}
  ],
  mainConfigFile: 'src/leave-absences/shared/config.js',
  generateSourceMaps: true,
  useSourceUrl: true,
  paths: {
    'common': 'empty:'
  },
  findNestedDependencies: true
});
