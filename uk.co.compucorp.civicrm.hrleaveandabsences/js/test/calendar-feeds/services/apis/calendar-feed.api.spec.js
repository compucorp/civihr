/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'leave-absences/calendar-feeds/services/apis/calendar-feed.api.data',
  'leave-absences/mocks/helpers/helper',
  'leave-absences/calendar-feeds/calendar-feeds.models'
], function (_, calendarFeedAPIData, mockHelper) {
  'use strict';

  describe('CalendarFeedAPI', function () {
    var $httpBackend, $rootScope, CalendarFeedAPI, expectedResults;

    beforeEach(module('calendar-feeds.models'));

    beforeEach(inject(['$httpBackend', '$rootScope', 'CalendarFeedAPI',
      function (_$httpBackend_, _$rootScope_, _CalendarFeedAPI_) {
        $httpBackend = _$httpBackend_;
        $rootScope = _$rootScope_;
        CalendarFeedAPI = _CalendarFeedAPI_;

        interceptHTTP();
      }
    ]));

    describe('all()', function () {
      var apiCallArgs;

      beforeEach(function () {
        spyOn(CalendarFeedAPI, 'sendGET').and.callThrough();
        CalendarFeedAPI
          .all()
          .then(function (results) {
            expectedResults = results;
          });
        $rootScope.$digest();
        $httpBackend.flush();

        apiCallArgs = CalendarFeedAPI.sendGET.calls.mostRecent().args;
      });

      it('calls the "LeaveRequestCalendarFeed.get" endpoint', function () {
        expect(apiCallArgs[0]).toBe('LeaveRequestCalendarFeedConfig');
        expect(apiCallArgs[1]).toBe('get');
      });

      it('fetches only enabled feeds', function () {
        expect(apiCallArgs[2].is_active).toBe(true);
      });

      it('returns expected data', function () {
        expect(expectedResults).toEqual(calendarFeedAPIData.all().values);
      });
    });

    /**
     * Intercept HTTP calls to be handled by httpBackend
     */
    function interceptHTTP () {
      // Intercept backend calls for GET CalendarFeedConfigAPI.get
      $httpBackend.whenGET(/action=get&entity=LeaveRequestCalendarFeedConfig/)
        .respond(calendarFeedAPIData.all());
    }
  });
});
