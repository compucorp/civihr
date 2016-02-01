/**
 * @name angular-date
 *
 * @author Krzysztof Kałamarski
 *
 * @extends ui.bootstrap
 *
 * @description Angular Module extending Date
 *
 * Temporally it's built as a CommonJs modules.
 * TODO make sure that all extensions are compatible with common dependencies and merge angular-date's AMD version.
 *
 * Library consists of set of directives, filters, services, templates and decorators extending the angular bootstrap UI module.
 *
 */

require("./src/templates/templates");

var Module = angular.module('angular-date', ['templates-main', 'ui.bootstrap']);

Module.service('DateValidationService', require('./src/services/DateValidationService'));
Module.factory('DateFactory', require('./src/services/DateFactory'));
Module.factory('DateFormatFactory', require('./src/services/DateFormatFactory'));
Module.filter('CustomDate', require('./src/filters/CustomDateFilter'));
Module.directive('customDateInput', require('./src/directives/CustomDateInput'));

/* Decorators */
Module.config(function ($provide) {
    $provide.decorator('datepickerPopupDirective', require('./src/decorators/DatepickerPopupDirectiveDecorator'));

    $provide.decorator('datepickerDirective', require('./src/decorators/DatepickerDirectiveDecorator'));

    $provide.decorator('daypickerDirective', require('./src/decorators/DaypickerDirectiveDecorator'));

    $provide.decorator('datepickerPopupWrapDirective', require('./src/decorators/DatepickerPopupWrapDirectiveDecorator'));
});
