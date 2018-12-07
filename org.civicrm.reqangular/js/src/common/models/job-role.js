/* eslint-env amd */

define([
  'common/moment',
  'common/modules/models',
  'common/models/contract',
  'common/models/model',
  'common/models/instances/job-role-instance',
  'common/services/api/job-role'
], function (moment, models) {
  'use strict';

  models.factory('JobRole', [
    '$q', 'Contract', 'Model', 'api.job-role', 'JobRoleInstance',
    function ($q, Contract, Model, jobRoleAPI, instance) {
      return Model.extend({

        /**
         * Returns a list of job roles instances
         *
         * @param  {Object} [filters] matching API params
         * @param  {Object} [pagination]
         * @param  {Number} pagination.page
         * @param  {Number} pagination.size
         * @param  {String} sort
         * @param  {Object} additionalParams
         * @param  {Boolean} cache
         * @return {Promise} resolve with [{JobRoleInstance}, ...]
         */
        all: function (filters, pagination, sort, additionalParams, cache) {
          return jobRoleAPI
            .all(this.processFilters(filters), pagination, sort, additionalParams, cache)
            .then(function (response) {
              response.list = response.list
                .map(function (jobRole) {
                  return instance.init(jobRole, true);
                });

              return response;
            });
        },

        /**
         * Finds a job role by id
         *
         * @param {string} id
         * @return {Promise} - Resolves with found job role
         */
        find: function (id) {
          return jobRoleAPI.find(id).then(function (jobRole) {
            return instance.init(jobRole, true);
          });
        },

        /**
         * Fetches active job roles for the current contract for a contact
         *
         * @param  {String} contactId
         * @return {Promise} resolves with [{JobRoleInstance}, ...]
         * if current job contract exists, otherwise with an empty array
         */
        activeForContact: function (contactId) {
          return Contract
            .activeForContact(contactId)
            .then(function (contracts) {
              var contractsIds = [];

              if (!contracts.length) {
                return $q.resolve([]);
              }

              contractsIds = contracts.map(function (contract) {
                return contract.id;
              });

              return this
                .all({ job_contract_id: { IN: contractsIds } })
                .then(function (jobRoles) {
                  return jobRoles.list.filter(function (jobRole) {
                    var endDate = moment(jobRole.end_date);
                    var hasEndDateAndIsActive = endDate.isValid() &&
                      moment().isSameOrBefore(endDate);
                    var doesNotHaveEndDate = endDate === undefined;

                    return doesNotHaveEndDate || hasEndDateAndIsActive;
                  });
                });
            }.bind(this));
        }
      });
    }
  ]);
});
