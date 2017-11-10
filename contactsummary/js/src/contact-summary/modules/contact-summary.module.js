/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/modules/contact-summary.config',
  'contact-summary/modules/contact-summary.constants',
  'contact-summary/modules/contact-summary.controllers',
  'contact-summary/modules/contact-summary.core',
  'contact-summary/modules/contact-summary.directives',
  'contact-summary/modules/contact-summary.run',
  'contact-summary/modules/contact-summary.services',
  'leave-absences/shared/components/leave-widget/leave-widget.component'
], function (angular) {
  angular.module('contactsummary', [
    'contactsummary.core',
    'contactsummary.config',
    'contactsummary.run',
    'contactsummary.constants',
    'contactsummary.controllers',
    'contactsummary.directives',
    'contactsummary.services',
    'leave-absences.components.leave-widget'
  ]);
});
