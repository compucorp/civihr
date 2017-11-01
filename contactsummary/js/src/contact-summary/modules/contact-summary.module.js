/* eslint-env amd */

define([
  'common/angular',
  'common/services/pub-sub',
  'contact-summary/modules/contact-summary.config',
  'contact-summary/modules/contact-summary.constants',
  'contact-summary/modules/contact-summary.controllers',
  'contact-summary/modules/contact-summary.directives',
  'contact-summary/modules/contact-summary.filters',
  'contact-summary/modules/contact-summary.run',
  'contact-summary/modules/contact-summary.services'
], function (angular) {
  angular.module('contactsummary', [
    'ngRoute',
    'ngResource',
    'ui.bootstrap',
    'common.services',
    'contactsummary.config',
    'contactsummary.run',
    'contactsummary.constants',
    'contactsummary.controllers',
    'contactsummary.directives',
    'contactsummary.filters',
    'contactsummary.services'
  ]);
});
