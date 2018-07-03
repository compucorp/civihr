/* eslint-env amd, jasmine */
/* global Drupal */

(function (Drupal) {
  define([
    'common/angular',
    'common/lodash',
    'common/angularMocks',
    'leave-absences/calendar-feeds/link-modal/link-modal.module'
  ], function (angular, _) {
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
        var hash = 'jahmaljahsurjahber';
        var expectedFeedUrl = Drupal.absoluteUrl('/') + 'civicrm/calendar-feed?hash=' + hash;

        beforeEach(function () {
          CalendarFeedsLinkModal.open(hash);
          $rootScope.$digest();
        });

        it('opens a medium sized modal', function () {
          expect($uibModal.open).toHaveBeenCalledWith(jasmine.objectContaining({
            size: 'md'
          }));
        });

        it('constructs and passes the url to the link modal component', function () {
          expect(calendarFeedsLinkModalComponent.url).toBe(expectedFeedUrl);
        });

        it('passes the dismiss function to the link modal component', function () {
          expect(calendarFeedsLinkModalComponent.dismiss).toEqual(jasmine.any(Function));
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
          },
          controller: function () {
            calendarFeedsLinkModalComponent = this;
          }
        });

        // removes any other link modal component that might have been defined
        // and only provides the mock one:
        $provide.decorator('calendarFeedsLinkModalDirective', function ($delegate) {
          var component = _.last($delegate);

          return [component];
        });
      }
    });
  });
}(Drupal));
