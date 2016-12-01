define([
    'appraisals/utils/routes',
    'common/angular',
    'common/angularBootstrap',
    'common/ui-select',
    'common/modules/dialog',
    'common/modules/directives',
    'common/modules/xeditable-civi',
    'common/filters/angular-date/format-date',
    'common/services/angular-date/date-format',
    'common/directives/loading',
    'appraisals/controllers/appraisals-ctrl',
    'appraisals/controllers/appraisals-dashboard-ctrl',
    'appraisals/controllers/appraisal-cycle-ctrl',
    'appraisals/controllers/modals/basic-modal-ctrl',
    'appraisals/controllers/modals/appraisal-cycle-modal-ctrl',
    'appraisals/controllers/modals/access-settings-modal-ctrl',
    'appraisals/controllers/modals/edit-dates-modal-ctrl',
    'appraisals/controllers/modals/add-contacts-modal-ctrl',
    'appraisals/controllers/modals/view-cycle-modal-ctrl',
    'appraisals/controllers/modals/send-notification-reminder-modal-ctrl',
    'appraisals/controllers/modals/notification-recipients-modal-ctrl',
    'appraisals/directives/show-more',
    'appraisals/directives/grades-chart',
    'appraisals/models/appraisal',
    'appraisals/models/appraisal-cycle',
    'appraisals/models/instances/appraisal-instance',
    'appraisals/models/instances/appraisal-cycle-instance',
    'appraisals/vendor/ui-router',
], function (routes, angular) {
    angular.module('appraisals', [
        'ngAnimate',
        'ngResource',
        'ui.router',
        'ui.bootstrap',
        'common.angularDate',
        'common.dialog',
        'common.directives',
        'appraisals.controllers',
        'appraisals.directives',
        'appraisals.models',
        'ui.select',
        'xeditable-civi'
    ])
    .config(['$stateProvider', '$urlRouterProvider', '$resourceProvider', '$httpProvider', '$logProvider',
        function ($stateProvider, $urlRouterProvider, $resourceProvider, $httpProvider, $logProvider) {
            $logProvider.debugEnabled(true);
            $resourceProvider.defaults.stripTrailingSlashes = false;
            $httpProvider.defaults.headers.common["X-Requested-With"] = 'XMLHttpRequest';

            routes($urlRouterProvider, $stateProvider);
        }
    ])
    .run(['$log', 'editableOptions', 'editableThemes',
        function ($log, editableOptions, editableThemes) {
            $log.debug('app.run');

            editableOptions.theme = 'bs3';
        }
    ]);

    return angular;
});
