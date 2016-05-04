define([
    'common/angular',
    'common/angularBootstrap',
    'common/ui-select',
    'common/directives/loading',
    'access-rights/controllers/access-rights-ctrl',
    'access-rights/controllers/access-rights-modal-ctrl',
    'access-rights/models/region',
    'access-rights/models/location',
], function (angular) {
    angular.module('access-rights', [
        'ui.bootstrap',
        'access-rights.controllers',
        'access-rights.models',
        'ui.select',
        'xeditable-civi'
    ])
    .run(['$log', 'editableOptions', 'editableThemes',
        function ($log, editableOptions, editableThemes) {
            $log.debug('app.run');
            editableOptions.theme = 'bs3';
        }
    ]);

    return angular;
});
