/* eslint-env amd, jasmine */

define([
  'leave-absences/calendar-feeds/models/calendar-feeds.calendar-feed.model',
  'leave-absences/calendar-feeds/apis/calendar-feeds.calendar-feed.api.mock',
  'leave-absences/calendar-feeds/instances/calendar-feeds.calendar-feed.instance'
], function () {
  'use strict';

  describe('CalendarFeedConfig', function () {
    var $provide, $rootScope, CalendarFeedConfig, CalendarFeedConfigAPI;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_CalendarFeedConfigAPIMock_) {
      $provide.value('CalendarFeedConfigAPI', _CalendarFeedConfigAPIMock_);
    }));

    beforeEach(inject(function (_CalendarFeedConfig_, _CalendarFeedConfigAPI_,
      _$rootScope_) {
      CalendarFeedConfig = _CalendarFeedConfig_;
      CalendarFeedConfigAPI = _CalendarFeedConfigAPI_;
      $rootScope = _$rootScope_;
    }));

    describe('all()', function () {
      beforeEach(function () {
        spyOn(CalendarFeedConfigAPI, 'all').and.callThrough();
        CalendarFeedConfig.all();
        $rootScope.$digest();
      });

      it('calls the equivalent API method', function () {
        expect(CalendarFeedConfigAPI.all).toHaveBeenCalledWith();
      });
    });
  });
});
