/* eslint-disable */

({
  baseUrl: 'src',
  dir: 'dist',
  wrapShim: true,
  modules: [
    { name: 'admin-dashboard' },
    { name: 'absence-tab' },
    { name: 'calendar-feeds-list' },
    { name: 'manager-leave' },
    { name: 'manager-notification-badge' },
    { name: 'my-leave' },
    { name: 'crm-app-list-absenceperiod' },
    { name: 'crm-app-list' },
    { name: 'crm-app-form-manage-entitlements' },
    { name: 'crm-app-form-absenceperiod' },
    { name: 'crm-app-form-workpattern' },
    { name: 'spectrum' }
  ],
  mainConfigFile: 'src/leave-absences/shared/config.js',
  generateSourceMaps: true,
  paths: {
    'common': 'empty:',
    'inputmask': 'leave-absences/crm/vendor/inputmask/inputmask.min',
    'inputmask.dependencyLib': 'leave-absences/crm/vendor/mocks/inputmask.dependencyLib',
    'jquery': 'leave-absences/crm/vendor/mocks/jquery'
  },
  findNestedDependencies: true
});
