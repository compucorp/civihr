/* eslint-disable */

({
  baseUrl: 'src',
  out: 'dist/job-roles.min.js',
  name: 'job-roles',
  skipModuleInsertion: true,
  generateSourceMaps: true,
  useSourceUrl: true,
  paths: {
    'common': 'empty:',
    'job-roles/vendor/angular-editable': 'job-roles/vendor/angular/xeditable.min',
    'job-roles/vendor/angular-filter': 'job-roles/vendor/angular/angular-filter.min'
  }
})
