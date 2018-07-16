/* eslint-env amd */

define([
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('WorkPatternInstance', ['$log', 'ModelInstance', function ($log, ModelInstance) {
    $log.debug('WorkPatternInstance');

    return ModelInstance.extend({});
  }]);
});
