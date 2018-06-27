/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/calendar-feeds/apis/calendar-feeds.calendar-feed.api.data',
  'leave-absences/calendar-feeds/instances/calendar-feeds.calendar-feed.instance',
  'leave-absences/shared/modules/models'
], function (_, calendarFeedConfigData) {
  'use strict';

  describe('CalendarFeedConfigInstance', function () {
    var CalendarFeedConfigInstance;

    beforeEach(module('leave-absences.models'));

    beforeEach(inject(function (_CalendarFeedConfigInstance_) {
      CalendarFeedConfigInstance = _CalendarFeedConfigInstance_;
    }));

    describe('init()', function () {
      var instance;
      var sampleFeed = calendarFeedConfigData.all().values[0];

      beforeEach(function () {
        instance = CalendarFeedConfigInstance.init(sampleFeed, true);
      });

      it('has initial attributes', function () {
        expect(_.every(sampleFeed, function (attributeValue, attributeName) {
          return instance[attributeName] === attributeValue;
        })).toBe(true);
      });
    });
  });
});
