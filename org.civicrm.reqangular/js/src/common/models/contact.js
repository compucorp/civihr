/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/models',
  'common/models/model',
  'common/models/contact-job-role.model',
  'common/models/group',
  'common/models/job-role',
  'common/models/session.model',
  'common/models/instances/contact-instance',
  'common/services/api/contact',
  'common/services/api/contract'
], function (_, models) {
  'use strict';

  models.factory('Contact', [
    '$q', 'api.contact', 'api.contract', 'ContactInstance', 'ContactJobRole', 'Group', 'JobRole', 'Model', 'Session',
    function ($q, contactAPI, jobContractAPI, instance, ContactJobRole, Group, JobRole, Model, Session) {
      var groupFiltersKeys = ['group_id'];
      var jobRoleFiltersKeys = ['region', 'department', 'level_type', 'location'];
      var jobContractFiltersKeys = ['with_contract_in_period'];

      /**
       * Checks if the given filters object contains filters
       * related to the foreign models
       *
       * @param  {Object} filters
       * @param  {Array} foreignKeys the keys that are for foreign models
       * @return {Boolean}
       */
      function containsForeignFilters (filters, foreignKeys) {
        return !_.isEmpty(_.intersection(_.keys(filters), foreignKeys));
      }

      /**
       * Returns IDs of contacts who have a job contract for the given period
       *
       * @param  {Array} period [startDate, endDate]
       * @return {Promise} resolves to {Array} of contact IDs
       */
      function getContactIdsWithContractsInPeriod (period) {
        return jobContractAPI.getContactsWithContractsInPeriod(
          period[0], period[1])
          .then(function (contactsWithContracts) {
            return _.map(contactsWithContracts, 'id');
          });
      }

      /**
       * Returns contact IDs of job roles that match the given filters
       *
       * @param  {Object} filters
       * @return {Promise} resolves to an array of contact IDs
       */
      function jobRoleContactIds (filters) {
        return ContactJobRole.all(filters)
          .then(function (contactJobRoles) {
            return contactJobRoles.map(function (contactJobRole) {
              return contactJobRole.contact_id;
            });
          });
      }

      /**
       * Adds a contact IDs list to the filters,
       * also removes all foreign filters belonging to other models.
       *
       * The final contact IDs list is the intersection (that is, an `AND`)
       * of all the contact IDs returned by the foreign models.
       *
       * @param  {Object} filters
       * @param  {Array} contactIds
       *   Coming from different promises (different models), this is
       *   an array of arrays
       * @return {Object}
       */
      function injectContactIdsInFilters (filters, contactIds) {
        return _(filters)
          .omit(groupFiltersKeys)
          .omit(jobRoleFiltersKeys)
          .omit(jobContractFiltersKeys)
          .assign({
            id: {in: _.intersection.apply(null, contactIds)}
          })
          .value();
      }

      /**
       * Processes the filters.
       *
       * It extracts any given foreign model specific filters, gets the
       * IDs of the contacts linked to models that match those filters,
       * and then uses those IDs as an additional filter.
       *
       * @param  {Object} filters
       * @return {Promise} resolves to the processed filters
       */
      function processContactFilters (filters) {
        var deferred = $q.defer();
        var promises = [];

        filters = this.compactFilters(filters);

        if (containsForeignFilters(filters, jobRoleFiltersKeys)) {
          promises.push(jobRoleContactIds(_.pick(filters, jobRoleFiltersKeys)));
        }

        if (containsForeignFilters(filters, groupFiltersKeys)) {
          if (filters.group_id) {
            promises.push(Group.contactIdsOf(filters.group_id));
          }
        }

        if (containsForeignFilters(filters, jobContractFiltersKeys)) {
          if (filters.with_contract_in_period) {
            promises.push(getContactIdsWithContractsInPeriod(filters.with_contract_in_period));
          }
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
         * @param  {Object} filters values the full list should be filtered by
         * @param  {Object} pagination
         * @param  {String} pagination.page for the current page
         * @param  {String} pagination.size for the number of items per page
         * @param  {String} sort
         * @param  {Object} additionalParams
         * @return {Promise}
         */
        all: function (filters, pagination, sort, additionalParams) {
          return processContactFilters.call(this, filters)
            .then(function (filters) {
              // In case of an empty array directly resolve the promise without calling the API
              if (filters && filters.id && !filters.id.IN.length) {
                return {list: []};
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
         * Finds a contact by ID
         *
         * @param  {String} id
         * @return {Promise} resolves to {ContactInstance}
         */
        find: function (id) {
          return contactAPI.find(id).then(function (contact) {
            return instance.init(contact, true);
          });
        },

        /**
         * Gets the currently logged in contact
         *
         * @return {Promise} resolves to {ContactInstance}
         */
        getLoggedIn: function () {
          return Session.get()
            .then(function (loggedInContact) {
              return this.find(loggedInContact.contactId);
            }.bind(this));
        },

        /**
         * Finds all the contacts managed by the passed contact ID
         *
         * @param  {String} id contact ID
         * @param  {Object} filters
         * @return {Promise} resolves to found contacts or API errors
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
