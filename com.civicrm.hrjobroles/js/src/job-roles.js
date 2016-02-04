(function () {
    var extPath = CRM.vars.hrjobroles.baseURL + '/js/src/job-roles';

    require.config({
        urlArgs: 'bust=' + (new Date()).getTime(),
        paths: {
            'job-roles': extPath,
            'job-roles/vendor/angular-editable': extPath + '/vendor/angular/xeditable.min',
            'job-roles/vendor/angular-filter': extPath + '/vendor/angular/angular-filter.min'
        }
    });

    require(['job-roles/app'], function (app) {
        'use strict';

        document.addEventListener('hrjobrolesLoad', function(){
            angular.bootstrap(document.getElementById('hrjobroles'), ['hrjobroles']);
        });
    });
})(require);
