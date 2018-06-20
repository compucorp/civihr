/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/models/calendar-feed-config.model',
  'leave-absences/mocks/apis/calendar-feed-config-api-mock',
  'leave-absences/shared/instances/calendar-feed-config.instance'
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
