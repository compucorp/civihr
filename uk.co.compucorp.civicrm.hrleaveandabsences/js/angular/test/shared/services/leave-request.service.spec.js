/* eslint-env amd, jasmine */

define([
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/shared/services/leave-request.service',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/leave-request-api-mock',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/manager-leave/app'
], function (leaveRequestData) {
  'use strict';

  describe('LeaveRequestService', function () {
    var $provide, $rootScope, dialog, LeaveRequestService;

    beforeEach(module('leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(function (_$rootScope_, _dialog_, _LeaveRequestService_) {
      $rootScope = _$rootScope_;
      dialog = _dialog_;
      LeaveRequestService = _LeaveRequestService_;
    }));

    describe('promptIfProceedWithBalanceChangeRecalculation()', function () {
      var proceedWithBalanceChangeRecalculation, resolved;

      beforeEach(function () {
        spyOn(dialog, 'open').and.callFake(function (params) {
          proceedWithBalanceChangeRecalculation = params.onConfirm;
        });
        LeaveRequestService.promptIfProceedWithBalanceChangeRecalculation()
          .then(function () {
            resolved = true;
          });
        $rootScope.$digest();
      });

      it('opens the dialog', function () {
        expect(dialog.open).toHaveBeenCalled();
      });

      it('does not resolve the promise yet', function () {
        expect(resolved).toBeFalsy();
      });

      describe('when user would like to proceed with balance change recalculation', function () {
        beforeEach(function () {
          proceedWithBalanceChangeRecalculation();
          $rootScope.$digest();
        });

        it('resolves the promise', function () {
          expect(resolved).toBeTruthy();
        });
      });
    });
  });
});
