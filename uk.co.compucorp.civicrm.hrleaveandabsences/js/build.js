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
    { name: 'crm-list-absenceperiod' },
    { name: 'crm-hrleaveandabsences' },
    { name: 'crm-form-manage-entitlements' },
    { name: 'crm-form-absenceperiod' },
    { name: 'crm-form-workpattern' },
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
