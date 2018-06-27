/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/calendar-feeds/apis/calendar-feeds.calendar-feed.api.data',
  'leave-absences/mocks/helpers/helper',
  'leave-absences/calendar-feeds/apis/calendar-feeds.calendar-feed.api'
], function (_, calendarFeedConfigData, mockHelper) {
  'use strict';

  describe('CalendarFeedConfigAPI', function () {
    var $httpBackend, $rootScope, CalendarFeedConfigAPI, expectedResults;

    beforeEach(module('leave-absences.apis'));
    beforeEach(inject(['$httpBackend', '$rootScope', 'CalendarFeedConfigAPI',
      function (_$httpBackend_, _$rootScope_, _CalendarFeedConfigAPI_) {
        $httpBackend = _$httpBackend_;
        $rootScope = _$rootScope_;
        CalendarFeedConfigAPI = _CalendarFeedConfigAPI_;

        interceptHTTP();
      }
    ]));

    describe('all()', function () {
      beforeEach(function () {
        spyOn(CalendarFeedConfigAPI, 'sendGET').and.callThrough();
        CalendarFeedConfigAPI
          .all()
          .then(function (results) {
            expectedResults = results;
          });
        $rootScope.$digest();
        $httpBackend.flush();
      });

      it('calls the "LeaveRequestCalendarFeedConfig.get" endpoint', function () {
        expect(CalendarFeedConfigAPI.sendGET.calls.mostRecent().args[0]).toBe('LeaveRequestCalendarFeedConfig');
        expect(CalendarFeedConfigAPI.sendGET.calls.mostRecent().args[1]).toBe('get');
      });

      it('returns expected data', function () {
        expect(expectedResults).toEqual(calendarFeedConfigData.all().values);
      });
    });

    /**
     * Intercept HTTP calls to be handled by httpBackend
     */
    function interceptHTTP () {
      // Intercept backend calls for GET CalendarFeedConfigAPI.all
      $httpBackend.whenGET(/action=get&entity=LeaveRequestCalendarFeedConfig/)
        .respond(calendarFeedConfigData.all());
    }
  });
});
