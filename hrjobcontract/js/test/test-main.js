
var TEST_REGEXP = /spec\.js$/i;
var allTestFiles = [];
var mocksPath = CRM.vars.hrjobcontract.baseURL + 'js/test/mocks';
var srcPath = CRM.vars.hrjobcontract.baseURL + 'js/src/job-contract';

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
    'leave-absences': CRM.vars.hrjobcontract.baseURL + '../uk.co.compucorp.civicrm.hrleaveandabsences/js/src/leave-absences',
    'leave-absences/mocks': CRM.vars.hrjobcontract.baseURL + '../uk.co.compucorp.civicrm.hrleaveandabsences/js/test/mocks',
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
