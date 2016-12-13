define([
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('AbsenceTypeInstance', ['$log', 'ModelInstance', function ($log, ModelInstance) {
    $log.debug('AbsenceTypeInstance');

    return ModelInstance.extend({});
  }]);
});
