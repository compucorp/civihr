define([
    'angularSelect',
    'controllers/controllers',
    'directives/directives',
    'filters/filters',
    'services/services'
], function () {
    return angular.module('hrjc',
        [
            'ngAnimate',
            'ngRoute',
            'ngResource',
            'ui.bootstrap',
            'ui.select',
            'angular-date',
            'angularFileUpload',
            'hrjc.controllers',
            'hrjc.directives',
            'hrjc.filters',
            'hrjc.services'
        ]
    );
});