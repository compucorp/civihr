define([
    'common/angular',
    'contact-summary/modules/contact-summary.config',
    'contact-summary/modules/contact-summary.constants',
    'contact-summary/modules/contact-summary.filters',
    'contact-summary/modules/contact-summary.run',
    'contact-summary/modules/contact-summary.services',
    'contact-summary/controllers/contact-summary.controller',
    'contact-summary/controllers/key-dates.controller',
    'contact-summary/controllers/key-details.controller',
    'contact-summary/directives/donut-chart.directive'
], function (angular) {
    var app = angular.module('contactsummary', [
        'ngRoute',
        'ngResource',
        'ui.bootstrap',
        'common.services',
        'contactsummary.config',
        'contactsummary.constants',
        'contactsummary.run',
        'contactsummary.controllers',
        'contactsummary.directives',
        'contactsummary.filters',
        'contactsummary.services'
    ]);
});
