/* eslint-env amd, jasmine */

define([
  'leave-absences/calendar-feeds/calendar-feed/calendar-feeds-calendar-feed.model',
  'leave-absences/calendar-feeds/calendar-feeds-calendar-feed.api.mock',
  'leave-absences/calendar-feeds/calendar-feeds-calendar-feed.instance'
], function () {
  'use strict';

  describe('CalendarFeed', function () {
    var $provide, $rootScope, CalendarFeed, CalendarFeedAPI;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_CalendarFeedAPIMock_) {
      $provide.value('CalendarFeedAPI', _CalendarFeedAPIMock_);
    }));

    beforeEach(inject(function (_CalendarFeed_, _CalendarFeedAPI_,
      _$rootScope_) {
      CalendarFeed = _CalendarFeed_;
      CalendarFeedAPI = _CalendarFeedAPI_;
      $rootScope = _$rootScope_;
    }));

    describe('all()', function () {
      beforeEach(function () {
        spyOn(CalendarFeedAPI, 'all').and.callThrough();
        CalendarFeed.all();
        $rootScope.$digest();
      });

      it('calls the equivalent API method', function () {
        expect(CalendarFeedAPI.all).toHaveBeenCalledWith();
      });
    });
  });
});
