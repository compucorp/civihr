/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/services/api.service',
  'contact-summary/services/contact-details.service',
  'contact-summary/services/contact.service',
  'contact-summary/services/contract.service',
  'contact-summary/services/item.service',
  'contact-summary/services/job-role.service',
  'contact-summary/services/leave.service',
  'contact-summary/services/model.service'
], function (angular, apiService, contactDetailsService, contactService,
  contractService, itemService, jobRoleService, leaveService, modelService) {
  'use strict';

  angular.module('contactsummary.services', [])
    .factory(apiService)
    .factory(contactDetailsService)
    .factory(contactService)
    .factory(contractService)
    .factory(itemService)
    .factory(jobRoleService)
    .factory(leaveService)
    .factory(modelService);
});
