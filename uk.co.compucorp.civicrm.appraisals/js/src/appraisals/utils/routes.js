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
                templateUrl: CRM.vars.appraisals.baseURL + '/views/dashboard.html'
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
