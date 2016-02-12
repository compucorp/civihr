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
            .state('dashboard', {
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
                    }],
                    statuses: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.statuses();
                    }],
                    types: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.types();
                    }]
                }
            })
            .state('appraisal-cycle', {
                url: '/appraisal-cycle/:cycleId',
                controller: 'AppraisalCycleCtrl',
                controllerAs: 'cycle',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/appraisal-cycle.html',
                resolve: {
                    types: ['AppraisalCycle', function (AppraisalCycle) {
                        return AppraisalCycle.types();
                    }]
                }
            })
            .state('profile', {
                url: '/profile',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/profile.html'
            })
            .state('import', {
                url: '/import',
                templateUrl: CRM.vars.appraisals.baseURL + '/views/import.html'
            });
    }
});
