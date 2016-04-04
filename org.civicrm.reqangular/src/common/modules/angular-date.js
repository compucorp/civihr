define([
    'common/angular',
    'common/decorators/angular-date/datepicker-popup',
    'common/decorators/angular-date/datepicker',
    'common/decorators/angular-date/datepicker-popup-wrap',
    'common/decorators/angular-date/daypicker',
    'common/modules/services',
    'common/angularBootstrap',
    'common/modules/templates'
], function (angular, datepickerPopup, datepicker, datepickerPopupWrap, daypicker) {
    'use strict';

    return angular.module("common.angularDate", ['common.services', 'ui.bootstrap', 'common.templates']).config(['$provide', function($provide){
        $provide.decorator('datepickerPopupDirective', datepickerPopup);
        $provide.decorator('datepickerPopupWrapDirective', datepickerPopupWrap);
        $provide.decorator('datepickerDirective', datepicker);
        $provide.decorator('daypickerDirective', daypicker);
    }]);
});
