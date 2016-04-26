define([
    'common/angular',
    'common/modules/services',
    'common/angularBootstrap',
    'common/modules/templates'
], function (angular, datepickerPopup, datepicker, datepickerPopupWrap, daypicker) {
    'use strict';

    return angular.module("common.angularDate", ['common.services', 'ui.bootstrap', 'common.templates']);
});
