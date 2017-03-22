define([
  'common/lodash',
  'leave-absences/shared/modules/models-instances',
  'leave-absences/shared/models/instances/leave-request-instance',
], function (_, modelInstances) {
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
          var sicknessCustomData = {
            sickness_reason: null,
            sickness_required_documents: '',
            request_type: 'sickness'
          };

          return _.assign({}, LeaveRequestInstance.defaultCustomData(), sicknessCustomData);
        },

        /**
         * Gets array of documents from comma separated string of documents
         *
         * @return {Array}
         */
        getDocumentArray: function () {
          var docsArray = this.sickness_required_documents ? this.sickness_required_documents.split(',') : [];

          return docsArray;
        },

        /**
         * Checks if given value is added for leave request list of document value ie., field required_documents
         *  otherwise add it to list of required documents (list is actually string of comma separated values for now)
         *
         * @param {String} documentValue required document value like '1'
         */
        toggleDocument: function (documentValue) {
          var docsArray = this.getDocumentArray();
          var index = docsArray.indexOf(documentValue);

          _.contains(docsArray, documentValue) ? docsArray.splice(index, 1) : docsArray.push(documentValue);
          this.sickness_required_documents = docsArray.join(',');
        },

        /**
         * Override of parent method
         *
         * @param {object} result - The accumulator object
         * @param {string} key - The property name
         */
        toAPIFilter: function (result, __, key) {
          if (!_.includes(['balance_change', 'dates', 'comments', 'uploader', 'files'], key)) {
            result[key] = this[key];
          }
        }
      });
    }
  ]);
});
