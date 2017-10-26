var TEST_REGEXP = /(spec|test)\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.jobContractTabApp.path + 'js/test/mocks';
var srcPath = CRM.jobContractTabApp.path + 'js/src/job-contract';

Object.keys(window.__karma__.files).forEach(function (file) {
  if (TEST_REGEXP.test(file)) {
    allTestFiles.push(file);
  }
});

require.config({
  deps: allTestFiles,
  waitSeconds: 60,
  paths: {
    'job-contract': srcPath,
    'job-contract/vendor/fraction': srcPath + '/vendor/fraction',
    'job-contract/vendor/job-summary': srcPath + '/vendor/jobsummary',
    'leave-absences': CRM.jobContractTabApp.path + '../uk.co.compucorp.civicrm.hrleaveandabsences/js/angular/src/leave-absences',
    'mocks': mocksPath
  },
  shim: {
    'job-contract/vendor/job-summary': {
      deps: ['common/moment']
    }
  },
  callback: function () {
    window.__karma__.start();
  }
});
