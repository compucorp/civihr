/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/components/leave-notification-badge.component'
], function () {
  'use strict';

  describe('leaveNotificationBadge', function () {
    var $componentController, $log, $rootScope, $q, controller, LeaveRequest;
    var eventName = 'some-event';

    beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave'));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _$q_, _LeaveRequest_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      LeaveRequest = _LeaveRequest_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      it('sets count to zero', function () {
        expect(controller.count).toBe(0);
      });

      it('sets shows the loader', function () {
        expect(controller.loading.count).toBe(true);
      });

      it('sets the event name same as the passed attribute', function () {
        expect(controller.eventName).toBe(eventName);
      });
    });

    describe('when filter data is initialized', function () {
      var filters = { list: 'somevalue' };
      var apiReturnValue = { list: [1, 2, 3] };

      beforeEach(function () {
        spyOn(LeaveRequest, 'all').and.returnValue($q.resolve(apiReturnValue));
        $rootScope.$broadcast('LeaveNotificationBadge:: Initialize Filters::' + eventName, filters);
      });

      it('calls Leave Request API to get the count', function () {
        expect(LeaveRequest.all).toHaveBeenCalledWith(filters);
      });

      describe('after api returns with value', function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        it('sets count to number of records returned', function () {
          expect(controller.count).toBe(apiReturnValue.list.length);
        });

        it('hides the loader', function () {
          expect(controller.loading.count).toBe(false);
        });
      });
    });

    function compileComponent () {
      controller = $componentController('leaveNotificationBadge', null, {
        eventName: eventName
      });
      $rootScope.$digest();
    }
  });
});
