({
  baseUrl: 'src',
  dir: 'dist',
  wrapShim: true,
  modules: [
    {name: 'admin-dashboard'},
    {name: 'absence-tab'},
    {name: 'my-leave'},
    {name: 'manager-leave'},
    {name: 'waiting-approval-notification'}
  ],
  mainConfigFile: 'src/leave-absences/shared/config.js',
  paths: {
    'common': 'empty:'
  },
  findNestedDependencies: true
});
