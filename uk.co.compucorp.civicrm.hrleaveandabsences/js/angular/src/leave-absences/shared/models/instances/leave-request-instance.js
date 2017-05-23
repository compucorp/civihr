define([
  'leave-absences/shared/modules/models-instances',
  'common/models/option-group',
  'common/models/instances/instance',
  'common/services/file-upload',
], function (instances) {
  'use strict';

  instances.factory('LeaveRequestInstance', ['$q', 'OptionGroup', 'FileUpload',
    'shared-settings', 'ModelInstance', 'LeaveRequestAPI',
    function ($q, OptionGroup, FileUpload, sharedSettings, ModelInstance, LeaveRequestAPI) {

      /**
       * Update status ID
       *
       * @param {string} status - name of the option value
       * @return {Promise} Resolved with {Object} - Error Data in case of error
       */
      function changeLeaveStatus(status) {
        return getOptionIDByName(status)
          .then(function (statusId) {
            this.status_id = statusId.value;
            return this.update();
          }.bind(this));
      }

      /**
       * Checks if a LeaveRequest is of a specific type
       *
       * @param {string} statusName - name of the option value
       * @return {Promise} Resolved with {Boolean}
       */
      function checkLeaveStatus(statusName) {
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
      function deleteAttachments() {
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
      function getOptionIDByName(name) {
        return OptionGroup.valuesOf('hrleaveandabsences_leave_request_status')
          .then(function (data) {
            return data.find(function (statusObj) {
              return statusObj.name === name;
            })
          })
      }

      /**
       * Save comments which do not have an ID and delete comments which are marked for deletion
       *
       * @return {Promise}
       */
      function saveAndDeleteComments() {
        var promises = [],
          self = this;

        //Save comments which dont have an ID
        self.comments.map(function (comment, index) {
          if (!comment.comment_id) {
            //IIFE is created to keep actual value of 'index' when promise is resolved
            (function (index) {
              promises.push(LeaveRequestAPI.saveComment(self.id, comment)
                .then(function (commentData) {
                  self.comments[index] = commentData;
                }));
            })(index);
          } else if (comment.toBeDeleted) {
            promises.push(LeaveRequestAPI.deleteComment(comment.comment_id));
          }
        });

        return $q.all(promises);
      }

      /**
       * Upload attachment in file uploder's queue
       *
       * @return {Promise}
       */
      function uploadAttachments() {
        if (this.fileUploader.queue && this.fileUploader.queue.length > 0) {
          return this.fileUploader.uploadAll({ entityID: this.id });
        } else {
          return $q.resolve([]);
        }
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
            request_type: 'leave',
            //FileUpload.uploader has uploader property which was causing circular reference issue
            //hence renamed this uploader to fileUploader
            fileUploader: FileUpload.uploader({
              entityTable: 'civicrm_hrleaveandabsences_leave_request',
              crmAttachmentToken: sharedSettings.attachmentToken,
              queueLimit: sharedSettings.fileUploader.queueLimit,
              allowedMimeTypes: sharedSettings.fileUploader.allowedMimeTypes
            })
          };
        },

        /**
         * Cancel a leave request
         */
        cancel: function () {
          return changeLeaveStatus.call(this, 'cancelled');
        },

        /**
         * Approve a leave request
         */
        approve: function () {
          return changeLeaveStatus.call(this, 'approved');
        },

        /**
         * Reject a leave request
         */
        reject: function () {
          return changeLeaveStatus.call(this, 'rejected');
        },

        /**
         * Sends a leave request back as more information is required
         */
        sendBack: function () {
          return changeLeaveStatus.call(this, 'more_information_required');
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
                uploadAttachments.call(this),
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
                saveAndDeleteComments.call(this),
                uploadAttachments.call(this)
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
          //If its an already saved comment, mark a toBeDeleted flag
          if (commentObj.comment_id) {
            commentObj.toBeDeleted = true;
            return;
          }

          this.comments = _.reject(this.comments, function (comment) {
            return commentObj.created_at === comment.created_at && commentObj.text === comment.text;
          });
        },

        /**
         * Delete a leave request
         *
         * @return {Promise} Resolved with {Object} Deleted Leave request
         */
        delete: function () {
          this.is_deleted = true;

          return LeaveRequestAPI.update(this.toAPI());
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
          return checkLeaveStatus.call(this, 'approved');
        },

        /**
         * Checks if a LeaveRequest is AwaitingApproval.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isAwaitingApproval: function () {
          return checkLeaveStatus.call(this, 'awaiting_approval');
        },

        /**
         * Checks if a LeaveRequest is cancelled.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isCancelled: function () {
          return checkLeaveStatus.call(this, 'cancelled');
        },

        /**
         * Checks if a LeaveRequest is Rejected.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isRejected: function () {
          return checkLeaveStatus.call(this, 'rejected');
        },

        /**
         * Checks if a LeaveRequest is Sent Back.
         *
         * @return {Promise} resolved with {Boolean}
         */
        isSentBack: function () {
          return checkLeaveStatus.call(this, 'more_information_required');
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
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'fileUploader', 'files'], key)) {
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
