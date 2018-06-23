/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.module'
], function (angular) {
  'use strict';

  describe('CalendarFeedsLinkModalService', function () {
    var $rootScope, $uibModal, CalendarFeedsLinkModal,
      calendarFeedsLinkModalComponent;

    beforeEach(angular.mock.module('calendar-feeds.link-modal', function ($compileProvider,
      $provide) {
      mockCalendarFeedsLinkModalComponent($compileProvider, $provide);
    }));

    beforeEach(inject(function (_$rootScope_, _$uibModal_, _CalendarFeedsLinkModal_) {
      $rootScope = _$rootScope_;
      $uibModal = _$uibModal_;
      CalendarFeedsLinkModal = _CalendarFeedsLinkModal_;

      spyOn($uibModal, 'open').and.callThrough();
    }));

    it('is defined', function () {
      expect(CalendarFeedsLinkModal).toBeDefined();
    });

    describe('open()', function () {
      var url = 'https://civihr.org/';

      beforeEach(function () {
        CalendarFeedsLinkModal.open(url);
        $rootScope.$digest();
      });

      it('opens a medium sized modal', function () {
        expect($uibModal.open).toHaveBeenCalledWith(jasmine.objectContaining({
          size: 'md'
        }));
      });

      it('passes the url to the link modal component', function () {
        expect(calendarFeedsLinkModalComponent.url).toBe(url);
      });

      it('passes the dismiss function to the link modal component', function () {
        expect(typeof calendarFeedsLinkModalComponent.dismiss).toBe('function');
      });
    });

    /**
     * Mocks the calendar feeds link modal component to test if bindings are
     * properly passed to it.
     *
     * @param {Object} $compileProvider - Angular's compile provider.
     * @param {Object} $provide - Angular's provide object.
     */
    function mockCalendarFeedsLinkModalComponent ($compileProvider, $provide) {
      $compileProvider.component('calendarFeedsLinkModal', {
        bindings: {
          dismiss: '<',
          url: '<'
        }
      });

      $provide.decorator('calendarFeedsLinkModalDirective', function ($delegate) {
        $delegate[0].controller = function () {
          calendarFeedsLinkModalComponent = this;
        };
        delete $delegate[0].templateUrl;

        return $delegate;
      });
    }
  });
});
