/* eslint-env amd */

define([
  'leave-absences/shared/modules/models-instances',
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  instances.factory('CalendarFeedConfigInstance', [
    'ModelInstance',
    function (ModelInstance) {
      return ModelInstance.extend({});
    }]);
});
