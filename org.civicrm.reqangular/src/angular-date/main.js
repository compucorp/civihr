var Module = angular.module('angular-date', []);

Module.service('DateValidationService', require('./src/services/DateValidationService'));
Module.filter('CustomDate', require('./src/filters/CustomDateFilter'));
Module.directive('customDateInput', require('./src/directives/CustomDateInput'));

