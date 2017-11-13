/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models-instances',
  'common/models/option-group',
  'common/models/instances/instance'
], function (_, instances) {
  'use strict';

  instances.factory('LeaveRequestInstance', ['$q', 'checkPermissions', 'OptionGroup',
    'shared-settings', 'ModelInstance', 'LeaveRequestAPI',
    function ($q, checkPermissions, OptionGroup, sharedSettings, ModelInstance, LeaveRequestAPI) {
      /**
       * Update status ID
       *
       * @param {string} status - name of the option value
       * @return {Promise} Resolved with {Object} - Error Data in case of error
       */
      function changeLeaveStatus (status) {
        return getOptionIDByName(status)
          .then(function (statusId) {
            var originalStatus = this.status_id;

            this.status_id = statusId.value;
            return this.update()
              .catch(function (error) {
                // Revert status id back in case of exception
                this.status_id = originalStatus;

                return $q.reject(error);
              }.bind(this));
          }.bind(this));
      }

      /**
       * Checks if a LeaveRequest is of a specific type
       *
       * @param {string} statusName - name of the option value
       * @return {Promise} Resolved with {Boolean}
       */
      function checkLeaveStatus (statusName) {
        return getOptionIDByName(statusName)
          .then(function (statusObj) {
            return this.status_id === statusObj.value;
          }.bind(this));
      }

      /**
       * Deletes the given attachment from server. It iterates through local
       * files array to find which are to be deleted and deletes them.
       *
       * @return {Promise}
       */
      function deleteAttachments () {
        var promises = [];

        _.forEach(this.files, function (file) {
          if (file.toBeDeleted) {
            promises.push(LeaveRequestAPI.deleteAttachment(this.id, file.attachment_id));
          }
        }.bind(this));

        return $q.all(promises);
      }

      /**
       * Get ID of an option value
       *
       * @param {string} name - name of the option value
       * @return {Promise} Resolved with {Object} - Specific leave request
       */
      function getOptionIDByName (name) {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (data) {
            return data.find(function (statusObj) {
              return statusObj.name === name;
            });
          });
      }

      /**
       * Save comments which do not have an ID and delete comments which are marked for deletion
       *
       * @return {Promise}
       */
      function saveAndDeleteComments () {
        var leaveRequestId = this.id;
        var promises = [];

        // Pushing a chain of API calls to create new comments sequentially
        promises.push($q.sequence(this.comments.filter(function (comment) {
          return !comment.comment_id;
        }).map(function (comment) {
          return function () {
            return LeaveRequestAPI.saveComment(leaveRequestId, comment);
          };
        })));

        // Deleting comments can done in parallel, no need in a promise chain
        promises = promises.concat(this.comments.filter(function (comment) {
          return comment.comment_id && comment.toBeDeleted;
        }).map(function (comment) {
          return LeaveRequestAPI.deleteComment(comment.comment_id);
        }));

        return $q.all(promises);
      }

      return ModelInstance.extend({

        /**
         * Returns the default custom data (as in, not given by the API)
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            comments: [],
            files: [],
            request_type: 'leave'
          };
        },

        /**
         * Gets the current balance change according to a current work pattern
         *
         * @param  {String} calculationUnit (days|hours)
         * @return {Promise} resolves to an object containing
         *   a balance change amount and a detailed breakdown
         */
        calculateBalanceChange: function (calculationUnit) {
          var params = ['contact_id', 'from_date', 'to_date', 'type_id', 'from_date_type', 'to_date_type'];

          if (calculationUnit === 'hours') {
            _.pull(params, 'from_date_type', 'to_date_type');
          }

          return LeaveRequestAPI.calculateBalanceChange(_.pick(this, params));
        },

        /**
         * Cancel a leave request
         */
        cancel: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.cancelled);
        },

        /**
         * Approve a leave request
         */
        approve: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.approved);
        },

        /**
         * Reject a leave request
         */
        reject: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.rejected);
        },

        /**
         * Sends a leave request back as more information is required
         */
        sendBack: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.moreInformationRequired);
        },

        /**
         * Update a leave request
         *
         * @return {Promise} Resolved with {Object} Updated Leave request
         */
        update: function () {
          return LeaveRequestAPI.update(this.toAPI())
            .then(function () {
              return $q.all([
                saveAndDeleteComments.call(this),
                deleteAttachments.call(this)
              ]);
            }.bind(this));
        },

        /**
         * Create a new leave request
         *
         * @return {Promise} Resolved with {Object} Created Leave request with
         *  newly created id for this instance
         */
        create: function () {
          return LeaveRequestAPI.create(this.toAPI())
            .then(function (result) {
              this.id = result.id;

              return $q.all([
                saveAndDeleteComments.call(this)
              ]);
            }.bind(this));
        },

        /**
         * Sets the flag to mark file for deletion. The file is not yet deleted
         * from the server.
         *
         * @param {Object} file - Attachment object
         */
        deleteAttachment: function (file) {
          if (!file.toBeDeleted) {
            file.toBeDeleted = true;
          }
        },

        /**
         * Removes a comment from memory
         *
         * @param {Object} commentObj - comment object
         */
        deleteComment: function (commentObj) {
          // If its an already saved comment, mark a toBeDeleted flag
          if (commentObj.comment_id) {
            commentObj.toBeDeleted = true;
            return;
          }

          this.comments = _.reject(this.comments, function (comment) {
            return commentObj.created_at === comment.created_at && commentObj.text === comment.text;
          });
        },

        /**
         * Deletes the leave request
         *
         * @return {Promise}
         */
        delete: function () {
          return LeaveRequestAPI.delete(this.id);
        },

        /**
         * Gets the balance change breakdown of the leave request
         *
         * @return {Promise}
         */
        getBalanceChangeBreakdown: function () {
          return LeaveRequestAPI.getBalanceChangeBreakdown(this.id)
            .then(function (response) {
              return {
                amount: _.reduce(response.values, function (sum, entry) {
                  return sum + parseFloat(entry.amount);
                }, 0),
                breakdown: response.values.map(function (entry) {
                  return {
                    amount: parseFloat(entry.amount),
                    date: entry.date,
                    type: {
                      id: entry.id,
                      value: entry.type,
                      label: entry.label
                    }
                  };
                })
              };
            });
        },

        /**
         * Gets info about work day for the date specified
         *   for the contact the leave request belongs to
         *
         * @param {String} date in the "YYYY-MM-DD" format
         */
        getWorkDayForDate: function (date) {
          return LeaveRequestAPI.getWorkDayForDate(date, this.contact_id)
            .then(function (response) {
              return response.values;
            })
            .catch(function (errors) {
              return $q.reject(errors);
            });
        },

        /**
         * Validate leave request instance attributes.
         *
         * @return {Promise} empty array if no error found otherwise an object
         *  with is_error set and array of errors
         */
        isValid: function () {
          return LeaveRequestAPI.isValid(this.toAPI());
        },

        /**
         * Checks if a LeaveRequest is Approved.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isApproved: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.approved);
        },

        /**
         * Checks if a LeaveRequest is AwaitingApproval.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isAwaitingApproval: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.awaitingApproval);
        },

        /**
         * Checks if a LeaveRequest is cancelled.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isCancelled: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.cancelled);
        },

        /**
         * Checks if a LeaveRequest is Rejected.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isRejected: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.rejected);
        },

        /**
         * Checks if a LeaveRequest is Sent Back.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isSentBack: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.moreInformationRequired);
        },

        /**
         * Loads comments for this leave request.
         *
         * @return {Promise}
         */
        loadComments: function () {
          if (this.id) {
            return LeaveRequestAPI.getComments(this.id)
              .then(function (comments) {
                this.comments = comments;
              }.bind(this));
          }

          return $q.resolve();
        },

        /**
         * Check the role of a given contact in relationship to the leave request.
         *
         * @param {Object} contactId
         * @return {Promise} resolves with an {String} - owner/admin/manager/none
         */
        roleOf: function (contactId) {
          return (this.contact_id === contactId)
            ? $q.resolve('owner')
            : checkPermissions(sharedSettings.permissions.admin.administer)
              .then(function (isAdmin) {
                return isAdmin
                  ? 'admin'
                  : LeaveRequestAPI.isManagedBy(this.id, contactId)
                    .then(function (isManager) {
                      return isManager ? 'manager' : 'none';
                    });
              }.bind(this));
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'files'], key)) {
            result[key] = this[key];
          }
        },

        /**
         * Loads file attachments associated with this leave request
         *
         * @return {Promise} with array of attachments if leave request is already created else empty promise
         */
        loadAttachments: function () {
          if (this.id) {
            return LeaveRequestAPI.getAttachments(this.id)
              .then(function (attachments) {
                this.files = attachments;
              }.bind(this));
          }

          return $q.resolve();
        }
      });
    }
  ]);
});
