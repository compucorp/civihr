/* eslint-env amd */
define([
  'common/lodash',
  'common/modules/models',
  'common/models/model',
  'common/models/group',
  'common/models/job-role',
  'common/models/instances/contact-instance',
  'common/services/api/contact'
], function (_, models) {
  'use strict';

  models.factory('Contact', [
    '$q', 'Model', 'api.contact', 'Group', 'JobRole', 'ContactInstance',
    function ($q, Model, contactAPI, Group, JobRole, instance) {
      var groupFiltersKeys = ['group_id'];
      var jobRoleFiltersKeys = ['region', 'department', 'level_type', 'location'];

      /**
       * Checks if the given filters object contains filters
       * related to the foreign models
       *
       * @param {object} filters
       * @param {Array} foreignKeys - The keys that are for foreign models
       * @return {boolean}
       */
      function containsForeignFilters (filters, foreignKeys) {
        return !_.isEmpty(_.intersection(_.keys(filters), foreignKeys));
      }

      /**
       * Returns the contact ids of the job roles that match the given filters
       *
       * @param {object} filters
       * @return {Promise} resolve to an array of contact ids
       */
      function jobRoleContactids (filters) {
        return JobRole.all(_.assign(filters, {
          'api.HRJobContract.getsingle': {
            'id': '$value.job_contract_id'
          }
        }))
          .then(function (jobRoles) {
            return jobRoles.list.map(function (jobRole) {
              return jobRole.contact_id;
            });
          });
      }

      /**
       * Adds the contact ids list to the filters, removing also all foreign
       * filters belonging to other models
       *
       * The final contact ids list is the intersection (that is, an `AND`)
       * of all the contact ids returned by the foreign models
       *
       * @param {object} filters
       * @param {Array} contactIds
       *   Coming from different promises (different models), this is
       *   an array of arrays
       * @return {object}
       */
      function injectContactIdsInFilters (filters, contactIds) {
        filters = _(filters)
          .omit(groupFiltersKeys)
          .omit(jobRoleFiltersKeys)
          .assign({
            id: {in: _.intersection.apply(null, contactIds)}
          })
          .value();

        return filters;
      }

      /**
       * Processes the filters
       *
       * It extracts any given foreign model specific filters, gets the
       * ids of the contacts linked to models that match those filters,
       * and then use those ids as an additional filter
       *
       * @param {object} filters
       * @return {Promise} resolves to the processed filters
       */
      function processContactFilters (filters) {
        var deferred = $q.defer();
        var promises = [];

        filters = this.compactFilters(filters);

        if (containsForeignFilters(filters, jobRoleFiltersKeys)) {
          promises.push(jobRoleContactids(_.pick(filters, jobRoleFiltersKeys)));
        }

        if (containsForeignFilters(filters, groupFiltersKeys)) {
          promises.push(Group.contactIdsOf(filters.group_id));
        }

        if (!_.isEmpty(promises)) {
          $q.all(promises)
            .then(function (results) {
              filters = injectContactIdsInFilters(filters, results);

              deferred.resolve(this.processFilters(filters));
            }.bind(this));
        } else {
          deferred.resolve(this.processFilters(filters));
        }

        return deferred.promise;
      }

      return Model.extend({

        /**
         * Returns a list of contacts, each converted to a model instance
         *
         * @param {object} filters - Values the full list should be filtered by
         * @param {object} pagination
         *   `page` for the current page, `size` for number of items per page
         * @param {string} sort
         * @param {object} additionalParams
         * @return {Promise}
         */
        all: function (filters, pagination, sort, additionalParams) {
          return processContactFilters.call(this, filters)
            .then(function (filters) {
              // if ID is empty array directly resolve the promise without calling the API
              if (filters && filters.id && !filters.id.IN.length) {
                return { list: [] };
              } else {
                return contactAPI.all(filters, pagination, sort, additionalParams);
              }
            })
            .then(function (response) {
              response.list = response.list.map(function (contact) {
                return instance.init(contact, true);
              });

              return response;
            });
        },

        /**
         * Finds a contact by id
         *
         * @param {string} id
         * @return {Promise} - Resolves with found contact
         */
        find: function (id) {
          return contactAPI.find(id).then(function (contact) {
            return instance.init(contact, true);
          });
        },

        /**
         * Finds all the contacts managed by the sent contact id
         *
         * @param {string} id - contact id
         * @param {object} filters
         * @return {Promise} - Resolves with found contacts/API Errors
         */
        leaveManagees: function (id, filters) {
          return processContactFilters.call(this, filters)
            .then(function (filters) {
              // if ID is empty array directly resolve the promise without calling the API
              if (filters && filters.id && !filters.id.IN.length) {
                return [];
              } else {
                return contactAPI.leaveManagees(id, filters);
              }
            });
        }
      });
    }
  ]);
});
