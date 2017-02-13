define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('LeaveRequestAPI', ['$log', 'api', '$q', function ($log, api, $q) {
    $log.debug('LeaveRequestAPI');

    /**
     * Checks if error is returned from server
     *
     * @param {Object} dataFromServer
     * @return {Boolean}
     */
    function checkError(dataFromServer) {
      return dataFromServer && !!dataFromServer.is_error;
    }

    /**
     * Checks given leave type and returns appropriate entity name
     *
     * @param {String} leaveRequestType whether its leave, sick or toil
     * @return {String}
     */
    function getEntityName(leaveRequestType) {
      var entityMap = {
        leave: 'LeaveRequest',
        sick: 'SicknessRequest',
        toil: 'TOILRequest'
      };
      leaveRequestType = !!leaveRequestType ? leaveRequestType : 'leave';

      return entityMap[leaveRequestType];
    }

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
       * @param  {Boolean} cache
       * @param {String} leaveRequestType whether its leave, sick or toil
       * @return {Promise} Resolved with {Object} All leave requests
       */
      all: function (filters, pagination, sort, params, cache, leaveRequestType) {
        $log.debug('LeaveRequestAPI.all');
        var entityName = getEntityName(leaveRequestType);

        return this.getAll(entityName, filters, pagination, sort, params, 'getFull', cache);
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
          deferred.reject('contact_id and period_id are mandatory');
        }

        var params = {
          contact_id: contactId,
          period_id: periodId,
          statuses: statuses ? { 'IN': statuses } : null,
          public_holiday: isPublicHoliday || false
        };

        this.sendGET('LeaveRequest', 'getbalancechangebyabsencetype', params, false)
          .then(function (data) {
            if (checkError(data)) {
              deferred.reject(data.error_message);
            } else {
              deferred.resolve(data.values);
            }
          });

        return deferred.promise;
      },

      /**
       * This method is used to update a leave request
       *
       * @param {object} params - Updated values of leave request
       * @param {String} leaveRequestType whether its leave, sick or toil
       * @return {Promise} Resolved with {Object} Updated Leave request
       */
      update: function (params, leaveRequestType) {
        $log.debug('LeaveRequestAPI.update', params);
        var deferred = $q.defer(),
          entityName = getEntityName(leaveRequestType);

        if (!params.id) {
          deferred.reject('id is mandatory field');
        }

        this.sendPOST(entityName, 'create', params)
          .then(function (data) {
            if (checkError(data)) {
              deferred.reject(data.error_message);
            } else {
              //returns array of single object hence getting first object
              deferred.resolve(data.values[0]);
            }
          });

        return deferred.promise;
      },

      /**
       * Gets the overall balance change after a leave request is created. The
       * API will create and return the detailed breakdown of it in days.
       *
       * @param {Object} params matched the API end point params like
       * mandatory values for contact_id, from_date, from_date_type and optional values for
       * to_date and to_date_type.
       *
       * @return {Promise} containing the detailed breakdown of balance leaves
       */
      calculateBalanceChange: function (params) {
        $log.debug('LeaveRequestAPI.calculateBalanceChange', params);
        var deferred = $q.defer();

        if (params && (!params.contact_id || !params.from_date || !params.from_date_type)) {
          deferred.reject('contact_id, from_date and from_date_type in params are mandatory');
        }

        this.sendPOST('LeaveRequest', 'calculatebalancechange', params)
          .then(function (data) {
            if (checkError(data)) {
              deferred.reject(data.error_message);
            } else {
              deferred.resolve(data.values);
            }
          });

        return deferred.promise;
      },

      /**
       * Create a new leave request with given params.
       *
       * @param {Object} params matched the API end point params with
       * mandatory values for contact_id, status_id, from_date, from_date_type
       * and optional values for to_date and to_date_type.
       * If to_date is given then to_date_type is also mandotory.
       *
       * @param {String} leaveRequestType whether its leave, sick or toil
       *
       * @return {Promise} containing the leave request object additionally with id key set
       * else rejects the promise with error data
       */
      create: function (params, leaveRequestType) {
        $log.debug('LeaveRequestAPI.create', params);
        var deferred = $q.defer(),
          entityName = getEntityName(leaveRequestType);

        if (params) {
          if (params.to_date && !params.to_date_type) {
            deferred.reject('to_date_type is mandatory');
          } else if (!params.contact_id || !params.from_date || !params.from_date_type || !params.status_id) {
            deferred.reject('contact_id, from_date, status_id and from_date_type params are mandatory');
          }
        }

        this.sendPOST(entityName, 'create', params)
          .then(function (data) {
            if (checkError(data)) {
              deferred.reject(data.error_message);
            } else {
              //returns array of single object hence getting first object
              deferred.resolve(data.values[0]);
            }
          });

        return deferred.promise;
      },

      /**
       * Validate params for a new new leave request. It can be used before
       * creating a leave request to validate data.
       *
       * @param {Object} params matched the API end point params with
       * values like contact_id, status_id, from_date, from_date_type etc.,
       *
       * @param {String} leaveRequestType whether its leave, sick or toil
       *
       * @return {Promise} returns an array of errors for invalid data else empty array
       */
      isValid: function (params, leaveRequestType) {
        $log.debug('LeaveRequestAPI.isValid', params);
        var deferred = $q.defer(),
          entityName = getEntityName(leaveRequestType);

        this.sendPOST(entityName, 'isValid', params)
          .then(function (data) {
            if (data.count > 0) {
              deferred.reject(data.values);
            } else {
              deferred.resolve(data.values);
            }
          });

        return deferred.promise;
      },

      /**
       * Calls the isManagedBy backend API.
       *
       * @param {String} leaveRequestID - Id of leave request
       * @param {String} contactID - Id of contact
       *
       * @return {Promise} resolves with an {Boolean}
       */
      isManagedBy: function (leaveRequestID, contactID) {
        $log.debug('LeaveRequestAPI.isManagedBy');

        var params = {
          leave_request_id: leaveRequestID,
          contact_id: contactID
        };

        return this.sendPOST('LeaveRequest', 'isManagedBy', params)
          .then(function (response) {
            return response.values;
          });
      }
    });
  }]);
});
