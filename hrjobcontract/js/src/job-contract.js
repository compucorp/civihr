(function () {
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
        'job-contract/app'
    ], function () {
        'use strict';

        document.addEventListener('hrjcInit', function(){
            angular.bootstrap(document.getElementById('hrjob-contract'), ['hrjc']);
        });

        document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('hrjcReady') : (function(){
            var e = document.createEvent('Event');
            e.initEvent('hrjcReady', true, true);
            return e;
        })());
    });
})(require);
