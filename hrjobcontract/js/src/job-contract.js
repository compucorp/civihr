(function (CRM, require) {
  var extPath = CRM.jobContractTabApp.path + 'js/src/job-contract';

  require.config({
    urlArgs: 'bust=' + (new Date()).getTime(),
    paths: {
      'job-contract': extPath,
      'job-contract/vendor/fraction': extPath + '/vendor/fraction',
      'job-contract/vendor/job-summary': extPath + '/vendor/jobsummary'
    },
    shim: {
      'job-contract/vendor/job-summary': {
        deps: ['common/moment']
      }
    }
  });

  require([
    'job-contract/modules/job-contract.module'
  ], function () {
    'use strict';

    document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('hrjcReady') : (function(){
      var e = document.createEvent('Event');
      e.initEvent('hrjcReady', true, true);
      return e;
    })());
  });
})(CRM, require);
