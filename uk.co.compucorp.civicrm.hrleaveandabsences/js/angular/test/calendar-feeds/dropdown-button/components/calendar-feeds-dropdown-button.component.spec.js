/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'leave-absences/calendar-feeds/services/apis/calendar-feed.api.data',
  'common/angularMocks',
  'leave-absences/calendar-feeds/dropdown-button/dropdown-button.module',
  'leave-absences/calendar-feeds/services/apis/calendar-feed.api.mock'
], function (angular, _, calendarFeedAPIData) {
  'use strict';

  describe('CalendarFeedsDropdownButton', function () {
    var $componentController, $provide, $q, $rootScope, CalendarFeed,
      CalendarFeedsDropdownButton, CalendarFeedsLinkModal;
    var dropdownPositionParameter = 'right';

    beforeEach(angular.mock.module('calendar-feeds.dropdown-button'));

    beforeEach(module('leave-absences.mocks', 'calendar-feeds.dropdown-button',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (CalendarFeedAPIMock) {
      $provide.value('CalendarFeed', CalendarFeedAPIMock);
    }));

    beforeEach(inject(function (_$componentController_, _$q_, _$rootScope_,
      _CalendarFeed_, _CalendarFeedsLinkModal_) {
      $componentController = _$componentController_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      CalendarFeed = _CalendarFeed_;
      CalendarFeedsLinkModal = _CalendarFeedsLinkModal_;
    }));

    beforeEach(function () {
      spyOn(CalendarFeed, 'all').and.callThrough();
    });

    describe('basic conditions', function () {
      beforeEach(function () {
        initComponent();
      });

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
        var allFeedsData = calendarFeedAPIData.all().values;

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
          expect(CalendarFeedsDropdownButton.feeds).toEqual(allFeedsData);
        });

        it('does not allow to create a feed', function () {
          expect(CalendarFeedsDropdownButton.canCreateNewFeed).toBe(false);
        });

        describe('when user chooses the feed', function () {
          var hash;

          beforeEach(function () {
            hash = allFeedsData[0].hash;

            spyOn(CalendarFeedsLinkModal, 'open');
            CalendarFeedsDropdownButton.openLinkModal(hash);
          });

          it('opens the Feed Link modal with the feed hash', function () {
            expect(CalendarFeedsLinkModal.open).toHaveBeenCalledWith(hash);
          });
        });
      });
    });

    describe('when user has a permission to administer feeds', function () {
      beforeEach(function () {
        $provide.value('checkPermissions', function (permission) {
          var permissions = ['can administer calendar feeds'];

          return $q.resolve(_.includes(permissions, permission));
        });

        initComponent();
        $rootScope.$digest();
      });

      it('allows to create a feed', function () {
        expect(CalendarFeedsDropdownButton.canCreateNewFeed).toBe(true);
      });
    });

    /**
     * Initiates the component and stores it for tests
     */
    function initComponent () {
      CalendarFeedsDropdownButton =
        $componentController('calendarFeedsDropdownButton', {}, {
          dropdownPosition: dropdownPositionParameter
        });
    }
  });
});
