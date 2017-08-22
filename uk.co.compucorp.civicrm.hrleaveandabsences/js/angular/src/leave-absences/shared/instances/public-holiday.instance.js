/* eslint-env amd */

define([
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('PublicHolidayInstance', ['$log', 'ModelInstance', function ($log, ModelInstance) {
    $log.debug('PublicHolidayInstance');

    return ModelInstance.extend({});
  }]);
});
