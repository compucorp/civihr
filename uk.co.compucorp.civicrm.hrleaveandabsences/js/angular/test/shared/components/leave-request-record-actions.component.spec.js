/* eslint-env amd, jasmine */

define([
  'leave-absences/manager-leave/app'
], function () {
  'use strict';

  describe('leaveRequestRecordActions', function () {
    var controller, $componentController, $log, $rootScope, LeavePopup;
    var contactId = '208';

    beforeEach(module('manager-leave'));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _LeavePopup_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $rootScope = _$rootScope_;
      LeavePopup = _LeavePopup_;

      spyOn($log, 'debug');

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      it('has contact ID', function () {
        expect(controller.contactId).toBe(contactId);
      });

      it('has leave request options', function () {
        var options = controller.leaveRequestOptions.map(function (option) {
          return option.type;
        });

        expect(options).toEqual(['leave', 'sickness', 'toil']);
      });
    });

    describe('openLeavePopup()', function () {
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';
      var isSelfRecord = true;

      beforeEach(function () {
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });
    });

    function compileComponent () {
      controller = $componentController('leaveRequestRecordActions', null, { contactId: contactId });
      $rootScope.$digest();
    }
  });
});
