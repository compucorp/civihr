/* eslint-env amd, jasmine */

define([
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/shared/models/leave-request.model',
  'leave-absences/shared/services/leave-request.service',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/leave-request-api-mock',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/manager-leave/app'
], function (leaveRequestData) {
  'use strict';

  describe('LeaveRequestService', function () {
    var $provide, $rootScope, $q, dialog, LeaveRequestService,
      LeaveRequestInstance;

    beforeEach(module('leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(function (_$rootScope_, _$q_, _dialog_,
      _LeaveRequestService_, _LeaveRequestInstance_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
      dialog = _dialog_;
      LeaveRequestService = _LeaveRequestService_;
      LeaveRequestInstance = _LeaveRequestInstance_;
    }));

    describe('checkIfBalanceChangeHasBeenChanged()', function () {
      var acknowledgeBalanceChange, leaveRequest, originalBalanceChangeAmount, status;

      beforeEach(function () {
        leaveRequest = LeaveRequestInstance.init(leaveRequestData.all()[0]);
        status = undefined;

        leaveRequest.getBalanceChangeBreakdown().then(function (balanceChange) {
          originalBalanceChangeAmount = balanceChange.amount;
        });
        spyOn(dialog, 'open').and.callFake(function (params) {
          acknowledgeBalanceChange = params.onConfirm;
        });
        $rootScope.$digest();
        dialog.open.calls.reset();
      });

      describe('when called', function () {
        var unit;

        beforeEach(function () {
          unit = 'days';

          spyOn(LeaveRequestInstance, 'calculateBalanceChange').and.callThrough();
          LeaveRequestService.checkIfBalanceChangeNeedsForceRecalculation(
            leaveRequest, unit, 0);
          $rootScope.$digest();
        });

        it('calls the calculateBalanceChange method of leave request instance', function () {
          expect(LeaveRequestInstance.calculateBalanceChange).toHaveBeenCalledWith(unit);
        });
      });

      describe('when balance change has not been changed', function () {
        beforeEach(function () {
          spyOn(LeaveRequestInstance, 'calculateBalanceChange').and.returnValue(
            $q.resolve({ amount: originalBalanceChangeAmount }));
          LeaveRequestService.checkIfBalanceChangeNeedsForceRecalculation(
            leaveRequest)
            .then(function (_status_) {
              status = _status_;
            });
          $rootScope.$digest();
        });

        it('does not open the dialog', function () {
          expect(dialog.open.calls.count()).toBe(0);
        });

        it('notifies that the balance change has not been changed', function () {
          expect(status).toBe(false);
        });
      });

      describe('when balance change has been changed', function () {
        beforeEach(function () {
          spyOn(LeaveRequestInstance, 'calculateBalanceChange').and.returnValue(
            $q.resolve({ amount: originalBalanceChangeAmount - 1 }));
          LeaveRequestService.checkIfBalanceChangeNeedsForceRecalculation(
            leaveRequest)
            .then(function (_status_) {
              status = _status_;
            });
          $rootScope.$digest();
        });

        it('opens the dialog', function () {
          expect(dialog.open.calls.count()).toBe(1);
        });

        it('does not yet notify about user acknowledgement', function () {
          expect(status).toBe(undefined);
        });

        describe('when user acknowledges the balance change', function () {
          beforeEach(function () {
            acknowledgeBalanceChange();
            $rootScope.$digest();
          });

          it('notifies about user acknowledgement', function () {
            expect(status).toBe(true);
          });
        });
      });
    });
  });
});
