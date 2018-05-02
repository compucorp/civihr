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
         * @param  {Object} filters Values the full list should be filtered by
         * @param  {Object} pagination
         *   `page` for the current page, `size` for number of items per page
         * @param  {String} sort The field and direction to order by
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
         * @param  {Object} params
         * @return {Promise} Resolves with {Object} Balance Change data
         */
        balanceChangeByAbsenceType: function (params) {
          return leaveRequestAPI.balanceChangeByAbsenceType(params);
        },

        /**
         * Get leave request for the given id
         *
         * @param {Object} id leave request id
         * @return {Promise} Resolves with {Object}
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
