/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/components/leave-notification-badge.component'
], function () {
  'use strict';

  describe('leaveNotificationBadge', function () {
    var $componentController, $log, $rootScope, $q, controller, LeaveRequest;
    var apiReturnValue = { list: [1, 2, 3] };
    var eventName = 'some-event';
    var filters = { list: 'somevalue' };

    beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'manager-leave'));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _$q_, _LeaveRequest_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      LeaveRequest = _LeaveRequest_;

      spyOn($log, 'debug');
      spyOn(LeaveRequest, 'all').and.returnValue($q.resolve(apiReturnValue));

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      it('sets the event name same as the passed attribute', function () {
        expect(controller.refreshCountEventName).toBe(eventName);
      });

      it('calls Leave Request API to get the count', function () {
        expect(LeaveRequest.all).toHaveBeenCalledWith(filters);
      });

      describe('after api returns with value', function () {
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
        refreshCountEventName: eventName,
        filters: filters
      });
      $rootScope.$digest();
    }
  });
});
