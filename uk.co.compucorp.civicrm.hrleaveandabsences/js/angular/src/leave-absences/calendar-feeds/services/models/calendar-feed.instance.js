/* eslint-env amd */

define(function () {
  'use strict';

  CalendarFeedInstance.__name = 'CalendarFeedInstance';
  CalendarFeedInstance.$inject = ['ModelInstance'];

  function CalendarFeedInstance (ModelInstance) {
    return ModelInstance.extend({});
  }

  return CalendarFeedInstance;
});
