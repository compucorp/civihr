define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('LeaveRequestAPI', ['$log', 'api', '$q', function ($log, api, $q) {
    $log.debug('LeaveRequestAPI');

    return api.extend({

      /**
       * This method returns all the Leave Requests.
       * It supports filters, pagination, sort and extra params
       *
       * @param {object} filters - Values the full list should be filtered by
       * @param {object} pagination
       *   `page` for the current page, `size` for number of items per page
       * @param {string} sort - The field and direction to order by
       * @param  {Object} params
       * @return {Promise} Resolved with {Object} All leave requests
       */
      all: function (filters, pagination, sort, params) {
        $log.debug('LeaveRequestAPI.all');

        return this.getAll('LeaveRequest', filters, pagination, sort, params, 'getFull');
      },

      /**
       * This method returns all the total change in balance that is caused by the
       * leave requests of a given absence type, or of all the absence types of a given contact and period.
       *
       * @param {string} contactId The ID of the Contact to get the balance change for
       * @param {string} periodId The ID of the Absence Period to get the balance change for
       * @param {array} [statuses = null] An array of OptionValue values which the list will be filtered by
       * @param {boolean} [isPublicHoliday=false] Based on the value of this param,
       * the calculation will include only the leave requests that aren't/are public holidays
       * @return {Promise} Resolved with {Object} Balance Change data or Error data
       */
      balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
        $log.debug('LeaveRequestAPI.balanceChangeByAbsenceType');
        var deferred = $q.defer();

        if (!contactId || !periodId) {
          deferred.resolve({
            is_error: 1,
            error_message: 'contact_id and period_id are mandatory'
          });
        }

        var params = {
          contact_id: contactId,
          period_id: periodId,
          statuses: statuses || null,
          public_holiday: isPublicHoliday || false
        };

        deferred.resolve(this.sendGET('LeaveRequest', 'getbalancechangebyabsencetype', params)
          .then(function (data) {
            return data.values;
          }));

        return deferred.promise;
      }
    });
  }]);
});
