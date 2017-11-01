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
], function (angular, ApiService, ContactDetailsService, ContactService, ContractService, ItemService, JobRoleService, LeaveService, ModelService) {
  'use strict';

  return angular.module('contactsummary.services', [])
    .factory(ApiService.__name, ApiService)
    .factory(ContactDetailsService.__name, ContactDetailsService)
    .factory(ContactService.__name, ContactService)
    .factory(ContractService.__name, ContractService)
    .factory(ItemService.__name, ItemService)
    .factory(JobRoleService.__name, JobRoleService)
    .factory(LeaveService.__name, LeaveService)
    .factory(ModelService.__name, ModelService);
});
