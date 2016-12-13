define([
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('LeaveRequestInstance', ['ModelInstance', 'LeaveRequestAPI', function (ModelInstance, LeaveRequestAPI) {

    return ModelInstance.extend({

      cancel: function () {
        LeaveRequestAPI.sendGET()
      }
    });
  }]);
});
