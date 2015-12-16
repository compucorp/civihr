require("../vendor/angular/ui-bootstrap-tpls");
require("./src/directives/templates");

var Module = angular.module('angular-date', ['templates-main', 'ui.bootstrap']);

Module.service('DateValidationService', require('./src/services/DateValidationService'));

Module.factory('DateFactory', require('./src/services/DateFactory'));

Module.filter('CustomDate', require('./src/filters/CustomDateFilter'));

Module.directive('customDateInput', require('./src/directives/CustomDateInput'));

/* Overrides */
Module.controller('DatePickerController', require('./src/controllers/DatePickerController'));

/* Decorators */
Module.config(function($provide) {
    $provide.decorator('datepickerPopupDirective', require('./src/decorators/DatepickerPopupDirectiveDecorator'));

    $provide.decorator('daypickerDirective', require('./src/decorators/DaypickerDirectiveDecorator'));

    $provide.decorator('datepickerPopupWrapDirective', require('./src/decorators/DatepickerPopupWrapDirectiveDecorator'));
});