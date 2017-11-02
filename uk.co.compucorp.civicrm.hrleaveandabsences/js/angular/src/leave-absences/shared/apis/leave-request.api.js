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
          var defer = $q.defer();

          // if contact_id has an empty array for IN condition, there is no point making the
          // call to the Leave Request API
          // TODO Move to Base API
          if (filters && filters.contact_id && filters.contact_id.IN && filters.contact_id.IN.length === 0) {
            defer.resolve({ list: [], total: 0, allIds: [] });
          } else {
            defer.resolve(this.getAll('LeaveRequest', filters, pagination, sort, params, 'getFull', cache));
          }

          return defer.promise;
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

          if (params && (!params.contact_id || !params.from_date /* || !params.from_date_type */)) {
            deferred.reject('contact_id, from_date and from_date_type in params are mandatory');
          }

          this.sendPOST('LeaveRequest', 'calculatebalancechange', params)
          .then(function (data) {
            // The breakdown property in the API response has been changed
            // from an array collection to an indexed collection (object),
            // so a transformation is needed to support the current code
            data.values.breakdown = _.values(data.values.breakdown);

            deferred.resolve(data.values);
          });

          return deferred.promise;
        },

        /**
         * Gets the balance change breakdown
         * @NOTE: This breakdown is not affected by a work pattern change
         *
         * @param  {Integer} leaveRequestId Leave Request ID
         * @return {Promise} resolves with the detailed balance breakdown
         */
        getBalanceChangeBreakdown: function (leaveRequestId) {
          return this.sendGET('LeaveRequest', 'getBreakdown',
            { leave_request_id: leaveRequestId }, false);
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

          /* if (params) {
            if (params.to_date && !params.to_date_type) {
              deferred.reject('to_date_type is mandatory');
            } else if (!params.contact_id || !params.from_date || !params.from_date_type || !params.status_id) {
              deferred.reject('contact_id, from_date, status_id and from_date_type params are mandatory');
            }
          } */

          this.sendPOST('LeaveRequest', 'create', params)
          .then(function (data) {
            deferred.resolve(data.values[0]);
          });

          return deferred.promise;
        },

        /**
         * Calls the `delete` endpoint with the given leave request id
         *
         * @param  {int/string} id
         * @return {Promise}
         */
        delete: function (id) {
          return this.sendPOST('LeaveRequest', 'delete', { id: id });
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
         * Get leave request for the given id
         *
         * @param {object} id - leave request id
         *
         * @return {Promise} resolves with {Object}
         */
        find: function (id) {
          $log.debug('LeaveRequestAPI.find');

          return this.sendGET('LeaveRequest', 'getFull', { id: id })
          .then(function (response) {
            if (response.values.length === 0) {
              return $q.reject('LeaveRequest not found with this ID');
            }

            return response.values[0];
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
         * Calls the isManagedBy backend API.
         *
         * @param {String} leaveRequestID - ID of leave request
         * @param {String} contactID - ID of contact
         * @return {Promise} resolves with an {Boolean}
         */
        isManagedBy: function (leaveRequestID, contactID) {
          $log.debug('LeaveRequestAPI.isManagedBy');

          return this.sendPOST('LeaveRequest', 'isManagedBy', {
            leave_request_id: leaveRequestID,
            contact_id: contactID
          })
          .then(function (response) {
            return response.values;
          });
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
            contact_id: comment.contact_id
          });

          return this.sendPOST('LeaveRequest', 'addcomment', params)
          .then(function (commentsData) {
            return commentsData.values;
          });
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

          return this.sendPOST('LeaveRequest', 'create', params)
          .then(function (data) {
            return data.values[0];
          });
        }
      });
    }]);
});
