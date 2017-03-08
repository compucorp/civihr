define([
  'common/modules/models-instances',
  'common/models/instances/instance',
  'common/services/api/contact',
], function (instances) {
  'use strict';

  instances.factory('ContactInstance', ['ModelInstance', 'api.contact',
    function (ModelInstance, ContactAPI) {
      return ModelInstance.extend({

        /**
         * Finds the contacts who are managed this contact
         *
         * @return {Promise}
         */
        leaveManagees: function () {
          return ContactAPI.leaveManagees(this.id);
        }
      });
    }]);
});
