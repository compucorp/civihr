(function () {
    var extPath = CRM.vars.appraisals.baseURL + '/js/src/appraisals';

    require.config({
        urlArgs: 'bust=' + (new Date()).getTime(),
        paths: {
            'appraisals': extPath,
            'appraisals/vendor/ui-router': extPath + '/vendor/angular-ui-router.min'
        }
    });

    require(['appraisals/app'], function () {
        angular.bootstrap(document.querySelector('[data-appraisals-app]'), ['appraisals']);
    });
})(require);
