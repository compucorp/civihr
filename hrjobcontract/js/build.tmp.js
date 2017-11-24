({
  baseUrl: 'src',
  out: 'dist/job-contract.min.js',
  name: 'job-contract',
  skipModuleInsertion: true,
  generateSourceMaps: true,
  useSourceUrl: true,
  paths: {
    'common': 'empty:',
    'job-contract/vendor/fraction': 'job-contract/vendor/fraction',
    'job-contract/vendor/job-summary': 'job-contract/vendor/jobsummary',
    'leave-absences': '/Users/bmn/buildkit/build/hr17-new/sites/all/modules/civicrm/tools/extensions/civihr/uk.co.compucorp.civicrm.hrleaveandabsences/js/angular/src/leave-absences'
  },
  shim: {
    'job-contract/vendor/job-summary': {
      deps: ['common/moment']
    }
  }
});
