/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/controllers/contact-summary.controller',
  'contact-summary/controllers/key-dates.controller',
  'contact-summary/controllers/key-details.controller'
], function (angular, ContactSummaryCtrl, KeyDatesCtrl, KeyDetailsCtrl) {
  'use strict';

  angular.module('contactsummary.controllers', [])
    .controller(ContactSummaryCtrl.__name, ContactSummaryCtrl)
    .controller(KeyDatesCtrl.__name, KeyDatesCtrl)
    .controller(KeyDetailsCtrl.__name, KeyDetailsCtrl);
});
