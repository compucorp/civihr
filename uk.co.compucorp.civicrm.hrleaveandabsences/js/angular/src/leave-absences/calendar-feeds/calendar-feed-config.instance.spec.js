/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/mocks/data/calendar-feed-config.data',
  './calendar-feeds.module'
], function (_, calendarFeedConfigData) {
  'use strict';

  describe('CalendarFeedConfigInstance', function () {
    var CalendarFeedConfigInstance;

    beforeEach(module('calendar-feeds'));

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
