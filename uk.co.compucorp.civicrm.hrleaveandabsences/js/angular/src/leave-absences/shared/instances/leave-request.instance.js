/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models-instances',
  'common/models/option-group',
  'common/models/instances/instance'
], function (_, instances) {
  'use strict';

  instances.factory('LeaveRequestInstance', ['$q', 'checkPermissions',
    'LeaveRequestAPI', 'ModelInstance', 'OptionGroup', 'shared-settings',
    function ($q, checkPermissions, LeaveRequestAPI, ModelInstance, OptionGroup, sharedSettings) {
      /**
       * Changes leave request status
       *
       * @param  {String} status - name of the option value
       * @return {Promise} resolves with {Object} or Error Data in case of error
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
       * Checks if the leave request has the given status
       *
       * @param  {String} statusName - name of the option value
       * @return {Promise} resolves with {Boolean}
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
       * Gets ID of an option value
       *
       * @param  {String} name - name of the option value
       * @return {Promise} Resolved with {Object} - Specific leave request
       */
      function getOptionIDByName (name) {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (data) {
            return _.find(data, function (statusObj) {
              return statusObj.name === name;
            });
          });
      }

      /**
       * Amends the first and last days of the balance by setting values from the
       * selected time deductions. It also re-calculates the total amount.
       *
       * @param {Object} balanceChange
       */
      function recalculateBalanceChange (balanceChange) {
        _.first(_.values(balanceChange.breakdown)).amount = this['from_date_amount'];

        if (balanceChange.breakdown.length > 1) {
          _.last(_.values(balanceChange.breakdown)).amount = this['to_date_amount'];
        }

        balanceChange.amount = _.reduce(balanceChange.breakdown,
          function (updatedChange, day) {
            return updatedChange - day.amount;
          }, 0);
      }

      /**
       * Saves comments which do not have an ID and
       * deletes comments which are marked for deletion
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
         * Approves the leave request
         */
        approve: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.approved);
        },

        /**
         * Gets the current balance change according to the current work pattern
         *
         * @param  {String} calculationUnit (days|hours)
         * @return {Promise} resolves to an object containing
         * a balance change amount and a detailed breakdown
         */
        calculateBalanceChange: function (calculationUnit) {
          var params = ['contact_id', 'from_date', 'to_date', 'type_id', 'from_date_type', 'to_date_type'];

          if (calculationUnit === 'hours') {
            _.pull(params, 'from_date_type', 'to_date_type');
          }

          return LeaveRequestAPI.calculateBalanceChange(_.pick(this, params))
            .then(function (balanceChange) {
              if (calculationUnit === 'hours') {
                recalculateBalanceChange.call(this, balanceChange);
              }

              return balanceChange;
            }.bind(this));
        },

        /**
         * Cancels the leave request
         */
        cancel: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.cancelled);
        },

        /**
         * Creates a new leave request by saving it to the server
         *
         * @return {Promise} resolves with {Object} Created Leave request with
         * the newly created ID for this instance
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
         * Deletes the leave request from the server
         *
         * @return {Promise}
         */
        delete: function () {
          return LeaveRequestAPI.delete(this.id);
        },

        /**
         * Sets the flag to mark file for deletion.
         * @NOTE The file is not yet deleted from the server.
         *
         * @param {Object} file - Attachment object
         */
        deleteAttachment: function (file) {
          if (!file.toBeDeleted) {
            file.toBeDeleted = true;
          }
        },

        /**
         * Flags a comment to be deleted if it is already saved on the server.
         * If it is not yet saved on the server, removes it from the collection.
         *
         * @param {Object} commentObject - comment object
         */
        deleteComment: function (commentObject) {
          if (commentObject.comment_id) {
            commentObject.toBeDeleted = true;

            return;
          }

          this.comments = _.reject(this.comments, function (comment) {
            return commentObject.created_at === comment.created_at &&
              commentObject.text === comment.text;
          });
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
         * for the contact the leave request belongs to
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
         * Checks if a LeaveRequest is Approved.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isApproved: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.approved);
        },

        /**
         * Checks if the leave request has the "Awaiting Approval" status
         *
         * @return {Promise} resolves with {Boolean}
         */
        isAwaitingApproval: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.awaitingApproval);
        },

        /**
         * Checks if the leave request is cancelled
         *
         * @return {Promise} resolves with {Boolean}
         */
        isCancelled: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.cancelled);
        },

        /**
         * Checks if the leave request is rejected
         *
         * @return {Promise} resolves with {Boolean}
         */
        isRejected: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.rejected);
        },

        /**
         * Checks if the leave request is sent back
         * (has "More Information Required status")
         *
         * @return {Promise} resolved with {Boolean}
         */
        isSentBack: function () {
          return checkLeaveStatus.call(this, sharedSettings.statusNames.moreInformationRequired);
        },

        /**
         * Validates the leave request instance attributes
         *
         * @return {Promise} empty array if no error found otherwise an object
         * with is_error set and array of errors
         */
        isValid: function () {
          return LeaveRequestAPI.isValid(this.toAPI());
        },

        /**
         * Loads file attachments associated with the leave request
         *
         * @return {Promise} resolves with array of attachments
         * if the leave request is already created, otherwise with an empty promise
         */
        loadAttachments: function () {
          if (this.id) {
            return LeaveRequestAPI.getAttachments(this.id)
              .then(function (attachments) {
                this.files = attachments;
              }.bind(this));
          }

          return $q.resolve();
        },

        /**
         * Loads comments for the leave request
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
         * Rejects the leave request
         */
        reject: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.rejected);
        },

        /**
         * Checks the role of a given contact in relationship to the leave request
         *
         * @param  {Object} contactId
         * @return {Promise} resolves with a {String} - owner/admin/manager/none
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
         * Sends the leave request back
         * (sets "More Information Required status)
         */
        sendBack: function () {
          return changeLeaveStatus.call(this, sharedSettings.statusNames.moreInformationRequired);
        },

        /**
         * Overrides the parent toAPIFilter() method
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
         * Updates the leave request
         *
         * @return {Promise} resolves with {Object} - updated leave request
         */
        update: function () {
          return LeaveRequestAPI.update(this.toAPI())
            .then(function () {
              return $q.all([
                saveAndDeleteComments.call(this),
                deleteAttachments.call(this)
              ]);
            }.bind(this));
        }
      });
    }
  ]);
});
