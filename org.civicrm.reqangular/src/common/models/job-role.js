define([
  'common/modules/models',
  'common/models/model',
  'common/models/instances/job-role-instance',
  'common/services/api/job-role'
], function (models) {
  'use strict';

  models.factory('JobRole', [
    'Model', 'api.job-role', 'JobRoleInstance',
    function (Model, jobRoleAPI, instance) {

      return Model.extend({

        /**
         * Returns a list of job roles, each converted to a model instance
         *
         * @param {object} filters - Values the full list should be filtered by
         * @param {object} pagination
         *   `page` for the current page, `size` for number of items per page
         * @return {Promise}
         */
        all: function (filters, pagination) {
          return jobRoleAPI.all(this.processFilters(filters), pagination).then(function (response) {
            response.list = response.list.map(function (jobRole) {
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
      });
    }
  ]);
})
