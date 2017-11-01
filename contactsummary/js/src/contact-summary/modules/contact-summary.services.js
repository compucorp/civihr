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
], function (angular, apiService, contactDetailsService, contactService, contractService, itemService, jobRoleService, leaveService, modelService) {
  'use strict';

  return angular.module('contactsummary.services', [])
    .factory(apiService.__name, apiService)
    .factory(contactDetailsService.__name, contactDetailsService)
    .factory(contactService.__name, contactService)
    .factory(contractService.__name, contractService)
    .factory(itemService.__name, itemService)
    .factory(jobRoleService.__name, jobRoleService)
    .factory(leaveService.__name, leaveService)
    .factory(modelService.__name, modelService);
});
