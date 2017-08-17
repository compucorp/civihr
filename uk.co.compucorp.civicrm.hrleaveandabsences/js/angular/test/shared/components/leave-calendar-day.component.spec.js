/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/components/leave-calendar-day.component',
  'leave-absences/manager-leave/app'
], function () {
  'use strict';

  describe('leaveCalendarDay', function () {
    var $componentController, $log, controller, LeavePopup;

    beforeEach(module('manager-leave'));
    beforeEach(inject(function (_$componentController_, _$log_, _LeavePopup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      LeavePopup = _LeavePopup_;

      spyOn($log, 'debug');
      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('openLeavePopup()', function () {
      var event;
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';
      var isSelfRecord = true;

      beforeEach(function () {
        event = jasmine.createSpyObj('event', ['stopPropagation']);
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(event, leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('stops the event from propagating', function () {
        expect(event.stopPropagation).toHaveBeenCalled();
      });
    });

    function compileComponent () {
      controller = $componentController('leaveCalendarDay');
    }
  });
});
