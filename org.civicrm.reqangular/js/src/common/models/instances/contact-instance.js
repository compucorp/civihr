/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/modules/models-instances',
  'common/models/instances/instance',
  'common/services/api/contact'
], function (_, instances) {
  'use strict';

  instances.factory('ContactInstance', ['ModelInstance', 'api.contact',
    function (ModelInstance, ContactAPI) {
      return ModelInstance.extend({

        /**
         * Finds the contacts who are managed this contact
         * @param {Object} params - any additional parameters
         * @return {Promise}
         */
        leaveManagees: function (params) {
          return ContactAPI.leaveManagees(this.id, params);
        },

        /**
         * Checks if the contact is a self leave approver
         *
         * @return {Promise} resolved with a {Boolean}
         */
        checkIfSelfLeaveApprover: function () {
          return this.leaveManagees()
            .then(function (contactManagees) {
              return !!_.find(contactManagees, { id: this.id });
            }.bind(this));
        }
      });
    }]);
});
