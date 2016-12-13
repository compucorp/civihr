define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('LeaveRequestAPI', ['$log', 'api', function ($log, api) {
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
       * @return {Promise}
       */
      all: function (filters, pagination, sort, params) {
        $log.debug('LeaveRequestAPI.all');

        return this.getAll('LeaveRequest', filters, pagination, sort, params, 'getFull')
          .then(function (data) {
            return data.values;
          });
      },

      /**
       * This method returns all the total change in balance that is caused by the
       * leave requests of a given absence type, or of all the absence types of a given contact and period.
       *
       * @param {string} contactId Mandatory The ID of the Contact to get the balance change for
       * @param {string} periodId Mandatory The ID of the Absence Period to get the balance change for
       * @param statuses {array} Optional (Default: null) An array of OptionValue values which the list will be filtered by
       * @param isPublicHoliday {boolean} Optional (Default: false) Based on the value of this param,
       * the calculation will include only the leave requests that aren't/are public holidays
       * @return {Promise}
       */
      balanceChangeByAbsenceType: function (contactId, periodId, statuses, isPublicHoliday) {
        $log.debug('LeaveRequestAPI.balanceChangeByAbsenceType');

        if(!contactId || !periodId) {
          throw "contact_id and period_id should have truthy value";
        }

        var params = {
          contact_id: contactId,
          period_id: periodId,
          statuses: statuses || null,
          public_holiday: isPublicHoliday || false
        };

        return this.sendGET('LeaveRequest', 'getbalancechangebyabsencetype', params)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
