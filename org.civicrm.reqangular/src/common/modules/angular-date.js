define([
    'common/angular',
    'common/decorators/angular-date/datepicker-popup',
    'common/modules/services',
    'common/angularBootstrap'
], function (angular, datepickerPopup) {
    'use strict';

    return angular.module("common.angularDate", ['common.services', 'ui.bootstrap']).config(['$provide', function($provide){
        $provide.decorator('datepickerPopupDirective', datepickerPopup);
    }]);
});
