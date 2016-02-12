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
                url: '/appraisal-cycle/:cycleId',
                controller: 'AppraisalCycleCtrl',
                controllerAs: 'cycle',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/appraisal-cycle.html'
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
