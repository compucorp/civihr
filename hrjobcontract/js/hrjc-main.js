var module, reqHrjc = require.config({
    context: 'hrjobcontract',
    baseUrl: CRM.jobContractTabApp.path + 'js',
    urlArgs: "bust=" + (new Date()).getTime(),
    paths: {
        fraction: 'vendor/fraction',
        moment: 'vendor/moment.min',
        jobSummary: 'vendor/jobsummary',
        requireLib: CRM.vars.reqAngular.requireLib
    },
    shim: {
        jobSummary: {
            deps: ['moment']
        }
    }
});

reqHrjc([
    'app',
    'services/contract',
    'controllers/contractList',
    'controllers/contract',
    'controllers/revisionList',
    'controllers/modal/modalChangeReason',
    'controllers/modal/modalContract',
    'controllers/modal/modalContractNew',
    'controllers/modal/modalDialog',
    'controllers/modal/modalProgress',
    'controllers/modal/modalRevision',
    'controllers/form/formGeneral',
    'controllers/form/formHour',
    'controllers/form/formHealth',
    'controllers/form/formPay',
    'controllers/form/formPension',
    'directives/contact',
    'directives/directives',
    'directives/loader',
    'directives/number',
    'directives/validate',
    'filters/capitalize',
    'filters/getObjById',
    'filters/formatAmount',
    'filters/formatPeriod',
    'filters/parseInt',
    'jobSummary',
    'requireLib'
],function(app){
    'use strict';

    app.constant('settings', {
        classNamePrefix: 'hrjc-',
        contactId: CRM.jobContractTabApp.contactId,
        debug: +CRM.debug,
        pathApp: CRM.jobContractTabApp.path,
        pathFile: CRM.url('civicrm/hrjobcontract/file/'),
        pathReport: CRM.url('civicrm/report/hrjobcontract/summary'),
        pathRest: CRM.url('civicrm/ajax/rest'),
        pathTpl: CRM.jobContractTabApp.path + 'views/',
        CRM: {
            options: CRM.FieldOptions || {},
            defaultCurrency: CRM.jobContractTabApp.defaultCurrency,
            apiTsFmt: 'YYYY-MM-DD HH:mm:ss',
            fields: CRM.jobContractTabApp.fields
        }
    });

    app.config(['settings','$routeProvider','$resourceProvider','$logProvider','$httpProvider',
        function(settings, $routeProvider, $resourceProvider, $logProvider, $httpProvider){
            $logProvider.debugEnabled(settings.debug);

            $routeProvider.
                when('/', {
                    controller: 'ContractListCtrl',
                    templateUrl: settings.pathApp+'views/contractList.html?v='+(new Date().getTime()),
                    resolve: {
                        contractList: ['ContractService',function(ContractService){
                            return ContractService.get()
                        }]
                    }
                }
            ).otherwise({redirectTo:'/'});

            $resourceProvider.defaults.stripTrailingSlashes = false;

            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';
        }
    ]);

    app.run(['settings','$rootScope','$q', '$log', 'ContractService', 'ContractDetailsService', 'ContractHourService',
        'ContractPayService', 'ContractLeaveService', 'ContractHealthService', 'ContractPensionService',
        function(settings, $rootScope, $q, $log, ContractService, ContractDetailsService, ContractHourService, ContractPayService,
                 ContractLeaveService, ContractHealthService, ContractPensionService){
            $log.debug('app.run');

            $rootScope.pathTpl = settings.pathTpl;
            $rootScope.prefix = settings.classNamePrefix;

            $q.all({
                contract: ContractService.getRevisionOptions(),
                details: ContractDetailsService.getOptions(),
                hour: ContractHourService.getOptions(),
                pay: ContractPayService.getOptions(),
                leave: ContractLeaveService.getOptions(),
                health: ContractHealthService.getOptions(),
                pension: ContractPensionService.getOptions()
            }).then(function(results){
                results.pay.pay_is_auto_est = ['No','Yes'];
                results.pension.is_enrolled = ['No','Yes','Opted out'];

                $log.debug('OPTIONS:');
                $log.debug(results);
                $rootScope.options = results;
            });
        }
    ]);

    document.addEventListener('hrjcInit', function(){
        angular.bootstrap(document.getElementById('hrjob-contract'), ['hrjc']);
    });

    document.dispatchEvent(typeof window.CustomEvent == "function" ? new CustomEvent('hrjcReady') : (function(){
        var e = document.createEvent('Event');
        e.initEvent('hrjcReady', true, true);
        return e;
    })());

});