define([
    'common/angular',
    'common/decorators/angular-date/datepicker-popup',
    'common/modules/services',
    'common/angularBootstrap',
    'common/modules/templates'
], function (angular, datepickerPopup) {
    'use strict';

    return angular.module("common.angularDate", ['common.services', 'ui.bootstrap', 'common.templates']).config([
        '$provide',
        function ($provide){
            $provide.decorator('uibDatepickerPopupDirective', datepickerPopup);
        }
    ]);
});
