/* eslint-env amd */

define([
  'common/models/instances/instance'
], function (instances) {
  'use strict';

  CalendarFeedConfigInstance.__name = 'CalendarFeedConfigInstance';
  CalendarFeedConfigInstance.$inject = ['ModelInstance'];

  return CalendarFeedConfigInstance;

  function CalendarFeedConfigInstance (ModelInstance) {
    return ModelInstance.extend({});
  }
});
