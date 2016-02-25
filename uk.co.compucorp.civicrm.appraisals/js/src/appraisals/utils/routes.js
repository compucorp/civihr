define(function () {

    /**
     * Adds the app's ui-router states
     *
     * @param {object} $urlRouterProvider
     * @param {object} $stateProvider
     */
    return function ($urlRouterProvider, $stateProvider) {
        $urlRouterProvider.otherwise("/dashboard");

        $stateProvider
            .state('appraisals', {
                abstract: true,
                template: '<ui-view/>',
                resolve: {
                    format: ['DateFormat', function (DateFormat) {
                        // Assigns date format to HR_settings.DATE_FORMAT under the hood
                        return DateFormat.getDateFormat();
                    }],
                    statuses: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.statuses();
                    }],
                    types: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.types();
                    }]
                }
            })
            .state('appraisals.dashboard', {
                url: '/dashboard',
                controller: 'AppraisalsDashboardCtrl',
                controllerAs: 'dashboard',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/dashboard.html',
                resolve: {
                    activeCycles: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.active();
                    }],
                    totalCycles: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.total();
                    }],
                    statusOverview: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.statusOverview();
                    }]
                }
            })
            .state('appraisals.appraisal-cycle', {
                abstract: true,
                url: '/appraisal-cycle/:cycleId',
                controller: 'AppraisalCycleCtrl',
                controllerAs: 'cycle',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/appraisal-cycle.html'
            })
            .state('appraisals.appraisal-cycle.cycle-summary', {
                url: '/appraisal-cycle/:cycleId/cycle-summary',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/appraisal-cycle/cycle-summary.html'
            })
            .state('appraisals.appraisal-cycle.appraisals-in-cycle', {
                url: '/appraisal-cycle/:cycleId/appraisals-in-cycle',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/appraisal-cycle/appraisals-in-cycle.html'
            })
            .state('appraisals.profile', {
                url: '/profile',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/profile.html'
            })
            .state('appraisals.import', {
                url: '/import',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/import.html'
            });
    }
});
