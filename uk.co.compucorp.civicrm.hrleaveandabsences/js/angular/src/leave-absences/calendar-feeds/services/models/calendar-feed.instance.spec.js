/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/calendar-feeds/calendar-feed.api.data',
  'leave-absences/calendar-feeds/calendar-feed.instance',
  'leave-absences/shared/modules/models'
], function (_, calendarFeedData) {
  'use strict';

  describe('CalendarFeedInstance', function () {
    var CalendarFeedInstance;

    beforeEach(module('leave-absences.models'));

    beforeEach(inject(function (_CalendarFeedInstance_) {
      CalendarFeedInstance = _CalendarFeedInstance_;
    }));

    describe('init()', function () {
      var instance;
      var sampleFeed = calendarFeedData.all().values[0];

      beforeEach(function () {
        instance = CalendarFeedInstance.init(sampleFeed, true);
      });

      it('has initial attributes', function () {
        expect(_.every(sampleFeed, function (attributeValue, attributeName) {
          return instance[attributeName] === attributeValue;
        })).toBe(true);
      });
    });
  });
});
