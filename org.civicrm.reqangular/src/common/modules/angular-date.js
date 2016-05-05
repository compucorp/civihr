define([
    'common/angular',
    'common/decorators/angular-date/datepicker-popup',
    'common/decorators/angular-date/date-filter',
    'common/modules/services',
    'common/angularBootstrap',
    'common/modules/templates'
], function (angular, datepickerPopup, dateFilter) {
    'use strict';

    return angular.module("common.angularDate", ['common.services', 'ui.bootstrap', 'common.templates']).config([
        '$provide',
        function ($provide){
            $provide.decorator('uibDatepickerPopupDirective', datepickerPopup);
            $provide.decorator('dateFilter', dateFilter);
        }
    ]);
});
