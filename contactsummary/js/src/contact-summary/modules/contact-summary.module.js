/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/controllers/contact-summary.controller',
  'contact-summary/controllers/key-dates.controller',
  'contact-summary/controllers/key-details.controller',
  'contact-summary/directives/donut-chart.directive',
  'contact-summary/services/api.service',
  'contact-summary/services/contact-details.service',
  'contact-summary/services/contact.service',
  'contact-summary/services/contract.service',
  'contact-summary/services/item.service',
  'contact-summary/services/job-role.service',
  'contact-summary/services/leave.service',
  'contact-summary/services/model.service',
  'contact-summary/modules/contact-summary.config',
  'contact-summary/modules/contact-summary.constants',
  'contact-summary/modules/contact-summary.core',
  'contact-summary/modules/contact-summary.run'
], function (angular, ContactSummaryController, KeyDatesController, KeyDetailsController,
  csDonutChart, apiService, contactDetailsService, contactService, contractService,
  itemService, jobRoleService, leaveService, modelService) {
  angular.module('contactsummary', [
    'contactsummary.core',
    'contactsummary.config',
    'contactsummary.run',
    'contactsummary.constants'
  ])
    .controller(ContactSummaryController)
    .controller(KeyDatesController)
    .controller(KeyDetailsController)
    .directive(csDonutChart)
    .factory(apiService)
    .factory(contactDetailsService)
    .factory(contactService)
    .factory(contractService)
    .factory(itemService)
    .factory(jobRoleService)
    .factory(leaveService)
    .factory(modelService);
});
