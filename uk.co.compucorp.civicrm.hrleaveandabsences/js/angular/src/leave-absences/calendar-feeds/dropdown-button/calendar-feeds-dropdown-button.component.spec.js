/* eslint-env amd, jasmine */

define([
  'common/angular',
  'leave-absences/calendar-feeds/calendar-feeds-calendar-feed.api.data',
  'common/angularMocks',
  'leave-absences/calendar-feeds/dropdown-button/calendar-feeds.dropdown-button.module'
], function (angular, calendarFeedAPIData) {
  'use strict';

  describe('CalendarFeedsDropdownButton', function () {
    var $provide, $rootScope, CalendarFeed, CalendarFeedsDropdownButton;
    var dropdownPositionParameter = 'right';

    beforeEach(angular.mock.module('calendar-feeds.dropdown-button'));

    beforeEach(module('leave-absences.mocks', 'calendar-feeds.dropdown-button', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (CalendarFeedAPIMock) {
      $provide.value('CalendarFeed', CalendarFeedAPIMock);
    }));

    beforeEach(inject(function (_$rootScope_, _CalendarFeed_) {
      $rootScope = _$rootScope_;
      CalendarFeed = _CalendarFeed_;
    }));

    beforeEach(function () {
      spyOn(CalendarFeed, 'all').and.callThrough();
    });

    beforeEach(inject(function ($componentController) {
      CalendarFeedsDropdownButton =
        $componentController('calendarFeedsDropdownButton', {}, {
          dropdownPosition: dropdownPositionParameter
        });
    }));

    it('is defined', function () {
      expect(CalendarFeedsDropdownButton).toBeDefined();
    });

    it('has a storage for feeds', function () {
      expect(CalendarFeedsDropdownButton.feeds).toEqual([]);
    });

    it('has a feeds loading state', function () {
      expect(CalendarFeedsDropdownButton.loading.feeds).toBe(true);
    });

    it('accepts the dropdown position parameter', function () {
      expect(CalendarFeedsDropdownButton.dropdownPosition)
        .toBe(dropdownPositionParameter);
    });

    describe('on init', function () {
      beforeEach(function () {
        $rootScope.$digest();
      });

      it('fetches calendar feeds', function () {
        expect(CalendarFeed.all).toHaveBeenCalledWith();
      });

      it('stops loading once the feeds fetch is completed', function () {
        expect(CalendarFeedsDropdownButton.loading.feeds).toBe(false);
      });

      it('stores feeds', function () {
        expect(CalendarFeedsDropdownButton.feeds).toEqual(calendarFeedAPIData.all().values);
      });
    });
  });
});
