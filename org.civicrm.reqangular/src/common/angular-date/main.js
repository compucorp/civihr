require("../vendor/angular/ui-bootstrap-tpls");
require("./src/directives/templates");

var Module = angular.module('angular-date', ['templates-main', 'ui.bootstrap']);

Module.service('DateValidationService', require('./src/services/DateValidationService'));
Module.factory('DateFactory', require('./src/services/DateFactory'));

Module.filter('CustomDate', require('./src/filters/CustomDateFilter'));

Module.directive('customDateInput', require('./src/directives/CustomDateInput'));

/* Overrides */
Module.controller('DatePickerController', require('./src/controllers/DatePickerController'));

Module.config(function($provide) {
    $provide.decorator('datepickerDirective', function($delegate) {
        var directive = $delegate[0];

        directive.controller = "DatePickerController";
        return $delegate;
    });
});