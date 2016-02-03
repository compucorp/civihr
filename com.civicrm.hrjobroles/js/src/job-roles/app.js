define([
    'job-roles/vendor/angular-editable',
    'job-roles/vendor/angular-filter',
    'job-roles/controllers/controllers',
    'job-roles/directives/directives',
    'job-roles/filters/filters',
    'job-roles/services/services'
], function () {
    'use strict';

    return angular.module('hrjobroles',
        [
            'ngAnimate',
            'angular-date',
            'ngRoute',
            'xeditable',
            'angular.filter',
            'ngResource',
            'ui.bootstrap',
            'hrjobroles.controllers',
            'hrjobroles.directives',
            'hrjobroles.filters',
            'hrjobroles.services'
        ]
    );
});
