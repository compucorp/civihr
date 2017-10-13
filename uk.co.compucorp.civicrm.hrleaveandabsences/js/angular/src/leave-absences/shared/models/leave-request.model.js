/* eslint-env amd */

define([
  'leave-absences/shared/modules/models',
  'common/models/model',
  'leave-absences/shared/apis/leave-request.api',
  'leave-absences/shared/instances/leave-request.instance'
], function (models) {
  'use strict';

  models.factory('LeaveRequest', [
    '$log',
    'Model',
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function ($log, Model, leaveRequestAPI, instance) {
      $log.debug('LeaveRequest');

      return Model.extend({

        /**
         * Get all the Leave Requests.
         * It supports filters, pagination, sort and extra params
         *
         * @param {object} filters - Values the full list should be filtered by
         * @param {object} pagination
         *   `page` for the current page, `size` for number of items per page
         * @param {string} sort - The field and direction to order by
         * @param  {Object} params
         * @param  {Boolean} cache
         * @return {Promise} resolves with {Object}
         */
        all: function (filters, pagination, sort, params, cache) {
          return leaveRequestAPI.all(this.processFilters(filters), pagination, sort, params, cache)
            .then(function (response) {
              response.list = response.list.map(function (leaveRequest) {
                return instance.init(leaveRequest, true);
              });

              return response;
            });
        },

        /**
         * Get all the total change in balance that is caused by the
         * leave requests of a given absence type, or of all the absence types of a given contact and period.
         *
         * @param {string} contactId The ID of the Contact to get the balance change for
         * @param {string} periodId The ID of the Absence Period to get the balance change for
         * @param statuses {array} An array of OptionValue values which the list will be filtered by
         * @param isPublicHoliday {boolean} Based on the value of this param,
         * the calculation will include only the leave requests that aren't/are public holidays
         * @return {Promise} Resolved with {Object} Balance Change data
         */
        balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
          return leaveRequestAPI.balanceChangeByAbsenceType(contactId, periodId, statuses, isPublicHoliday);
        },

        /**
         * Get leave request for the given id
         *
         * @param {object} id - leave request id
         *
         * @return {Promise} resolves with {Object}
         */
        find: function (id) {
          return leaveRequestAPI.find(id)
            .then(function (leaveRequest) {
              return instance.init(leaveRequest, true);
            });
        }
      });
    }
  ]);
});
