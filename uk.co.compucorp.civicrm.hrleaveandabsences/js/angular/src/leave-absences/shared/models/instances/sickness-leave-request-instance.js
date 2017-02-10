define([
  'leave-absences/shared/modules/models-instances',
  'common/lodash',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (modelInstances, _) {
  'use strict';

  modelInstances.factory('SicknessRequestInstance', [
    'LeaveRequestAPI',
    'LeaveRequestInstance',
    function (LeaveRequestAPI, LeaveRequestInstance) {
      return LeaveRequestInstance.extend({

        /**
         * Returns the default custom data
         * with its default values
         *
         * @return {object}
         */
        defaultCustomData: function () {
          return {
            reason: null,
            required_documents: ''
          }
        },

        /**
         * Create a new sickness request
         *
         * @return {Promise} Resolved with {Object} Created Leave request with
         *  newly created id for this instance
         */
        create: function () {
          return LeaveRequestAPI.create(this.toAPI(), 'sick')
            .then(function (result) {
              this.id = result.id;
            }.bind(this));
        },

        /**
         * Validate sickness request instance attributes.
         *
         * @return {Promise} empty array if no error found otherwise an object
         *  with is_error set and array of errors
         */
        isValid: function () {
          return LeaveRequestAPI.isValid(this.toAPI(), 'sick');
        },

        /**
         * Update a sickness request
         *
         * @return {Promise} Resolved with {Object} Updated sickness request
         */
        update: function () {
          return LeaveRequestAPI.update(this.toAPI(), 'sick');
        },

        /**
         * Checks if given value is added for leave request list of document value ie., field required_documents
         *  otherwise add it to list of required documents (list is actually string of comma separated values for now)
         *
         * @param {String} documentValue required document value like '1'
         */
        toggleDocument: function (documentValue) {
          var docsArray = this.required_documents ? this.required_documents.split(','): [];
          var index = docsArray.indexOf(documentValue);

          _.contains(docsArray, documentValue) ? docsArray.splice(index, 1) : docsArray.push(documentValue);
          this.required_documents = docsArray.join(',');
        }
      });
    }
  ]);
});
