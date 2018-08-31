/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/controllers/contact-summary.controller',
  'contact-summary/controllers/key-dates.controller',
  'contact-summary/controllers/key-details.controller'
], function (angular, ContactSummaryController, KeyDatesController, KeyDetailsController) {
  'use strict';

  angular.module('contactsummary.controllers', [])
    .controller(ContactSummaryController)
    .controller(KeyDatesController)
    .controller(KeyDetailsController);
});
