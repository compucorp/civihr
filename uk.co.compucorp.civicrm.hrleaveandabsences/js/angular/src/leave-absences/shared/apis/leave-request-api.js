/* eslint-env amd */
define([
  'leave-absences/shared/modules/apis',
  'common/lodash',
  'common/services/api'
], function (apis, _) {
  'use strict';

  apis.factory('LeaveRequestAPI', ['$log', 'api', '$q', 'shared-settings',
    function ($log, api, $q) {
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
       * @param  {Boolean} cache
       * @return {Promise} Resolved with {Object} All leave requests
       */
        all: function (filters, pagination, sort, params, cache) {
          $log.debug('LeaveRequestAPI.all');

          return this.getAll('LeaveRequest', filters, pagination, sort, params, 'getFull', cache);
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
            statuses: statuses ? {'IN': statuses} : null,
            public_holiday: isPublicHoliday || false
          };

          this.sendGET('LeaveRequest', 'getbalancechangebyabsencetype', params, false)
          .then(function (data) {
            deferred.resolve(data.values);
          });

          return deferred.promise;
        },

      /**
       * This method is used to update a leave request
       *
       * @param {object} params - Updated values of leave request
       * @return {Promise} Resolved with {Object} Updated Leave request
       */
        update: function (params) {
          $log.debug('LeaveRequestAPI.update', params);
          var deferred = $q.defer();

          if (!params.id) {
            deferred.reject('id is mandatory field');
          }

          this.sendPOST('LeaveRequest', 'create', params)
          .then(function (data) {
            deferred.resolve(data.values[0]);
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
            deferred.resolve(data.values);
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
       * @return {Promise} containing the leave request object additionally with id key set
       * else rejects the promise with error data
       */
        create: function (params) {
          $log.debug('LeaveRequestAPI.create', params);
          var deferred = $q.defer();

          if (params) {
            if (params.to_date && !params.to_date_type) {
              deferred.reject('to_date_type is mandatory');
            } else if (!params.contact_id || !params.from_date || !params.from_date_type || !params.status_id) {
              deferred.reject('contact_id, from_date, status_id and from_date_type params are mandatory');
            }
          }

          this.sendPOST('LeaveRequest', 'create', params)
          .then(function (data) {
            deferred.resolve(data.values[0]);
          });

          return deferred.promise;
        },

      /**
       * Validate params for a new new leave request. It can be used before
       * creating a leave request to validate data.
       *
       * @param {Object} params matched the API end point params with
       * values like contact_id, status_id, from_date, from_date_type etc.,
       * @return {Promise} returns an array of errors for invalid data else empty array
       */
        isValid: function (params) {
          $log.debug('LeaveRequestAPI.isValid', params);
          var deferred = $q.defer();

          this.sendPOST('LeaveRequest', 'isValid', params)
          .then(function (data) {
            if (data.count > 0) {
              deferred.reject(_(data.values).map().flatten().value());
            } else {
              deferred.resolve(data.values);
            }
          });

          return deferred.promise;
        },

      /**
       * Calls the getcomment backend API.
       *
       * @param {String} leaveRequestID - ID of leave request
       * @param {Object} params
       *
       * @return {Promise}
       */
        getComments: function (leaveRequestID, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID
          });

          return this.sendGET('LeaveRequest', 'getcomment', params, false)
          .then(function (commentsData) {
            return commentsData.values;
          });
        },

      /**
       * Calls the addcomment backend API.
       *
       * @param {string} leaveRequestID - ID of Leave Request
       * @param {Object} comment - Comment object
       * @param {Object} params
       *
       * @return {Promise}
       */
        saveComment: function (leaveRequestID, comment, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID,
            text: comment.text,
            contact_id: comment.contact_id,
            created_at: comment.created_at
          });

          return this.sendPOST('LeaveRequest', 'addcomment', params)
          .then(function (commentsData) {
            return commentsData.values;
          });
        },

      /**
       * Calls the deletecomment backend API.
       *
       * @param {String} commentID - comment ID
       * @param {Object} params
       *
       * @return {Promise}
       */
        deleteComment: function (commentID, params) {
          params = _.assign({}, params, {
            comment_id: commentID
          });

          return this.sendPOST('LeaveRequest', 'deletecomment', params)
          .then(function (commentsData) {
            return commentsData.values;
          });
        },

      /**
       * Calls the getattachments backend API.
       *
       * @param {String} leaveRequestID - ID of leave request
       * @param {Object} params
       *
       * @return {Promise}
       */
        getAttachments: function (leaveRequestID, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID
          });

          return this.sendGET('LeaveRequest', 'getattachments', params, false)
          .then(function (attachments) {
            return attachments.values;
          });
        },

      /**
       * Calls the deletecomment backend API.
       *
       * @param {String} leaveRequestID - leave request ID
       * @param {String} attachmentID - attachment ID
       * @param {Object} params
       *
       * @return {Promise}
       */
        deleteAttachment: function (leaveRequestID, attachmentID, params) {
          params = _.assign({}, params, {
            leave_request_id: leaveRequestID,
            attachment_id: attachmentID
          });

          return this.sendPOST('LeaveRequest', 'deleteattachment', params)
          .then(function (result) {
            return result.values;
          });
        }
      });
    }]);
});
