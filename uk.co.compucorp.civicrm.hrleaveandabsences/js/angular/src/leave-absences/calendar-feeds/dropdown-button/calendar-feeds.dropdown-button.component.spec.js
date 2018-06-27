/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'leave-absences/calendar-feeds/dropdown-button/calendar-feeds.dropdown-button.module'
], function (angular) {
  'use strict';

  describe('CalendarFeedsDropdownButton', function () {
    var CalendarFeedsDropdownButton;
    var dropdownPositionParameter = 'right';

    beforeEach(angular.mock.module('calendar-feeds.dropdown-button'));

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
      expect(CalendarFeedsDropdownButton.loading.feeds).toBeDefined();
    });

    it('accepts the dropdown position parameter', function () {
      expect(CalendarFeedsDropdownButton.dropdownPosition)
        .toBe(dropdownPositionParameter);
    });
  });
});
