/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.module'
], function (angular, _) {
  'use strict';

  describe('CalendarFeedsLinkModalService', function () {
    var $document, $rootScope, $uibModal, CalendarFeedsLinkModal,
      calendarFeedsLinkModalComponent;
    var url = 'https://civihr.org/';

    beforeEach(angular.mock.module('calendar-feeds.link-modal', function ($compileProvider,
      $provide) {
      mockCalendarFeedsLinkModalComponent($compileProvider, $provide);
    }));

    beforeEach(inject(function (_$document_, _$rootScope_, _$uibModal_,
      _CalendarFeedsLinkModal_) {
      $document = _$document_;
      $rootScope = _$rootScope_;
      $uibModal = _$uibModal_;
      CalendarFeedsLinkModal = _CalendarFeedsLinkModal_;

      spyOn($uibModal, 'open').and.callThrough();
    }));

    it('is defined', function () {
      expect(CalendarFeedsLinkModal).toBeDefined();
    });

    describe('open()', function () {
      beforeEach(function () {
        CalendarFeedsLinkModal.open(url);
        $rootScope.$digest();
      });

      afterEach(function () {
        $document.find('body').empty();
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
        expect(calendarFeedsLinkModalComponent.dismiss).toEqual(jasmine.any(Function));
      });

      describe('when there is a bootstrap theme element', function () {
        beforeEach(function () {
          $uibModal.open.calls.reset();
          $document.find('body').append('<div id="bootstrap-theme"></div>');
          CalendarFeedsLinkModal.open(url);
          $rootScope.$digest();
        });

        it('appends the modal to the bootstrap theme element', function () {
          expect($uibModal.open.calls.mostRecent().args[0].appendTo).toEqual(
            $document.find('#bootstrap-theme').eq(0)
          );
        });
      });

      describe('when there is not a bootstrap theme element', function () {
        beforeEach(function () {
          $uibModal.open.calls.reset();
          CalendarFeedsLinkModal.open(url);
          $rootScope.$digest();
        });

        it('appends the modal to the body element', function () {
          expect($uibModal.open.calls.mostRecent().args[0].appendTo).toEqual(
            $document.find('body').eq(0)
          );
        });
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
