/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/absence-period.data',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/mocks/data/option-group.data',
  'leave-absences/mocks/helpers/helper',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, absencePeriodData, absenceTypeData, leaveRequestData, optionGroupMock, helper) {
  'use strict';

  describe('RequestModalDetailsToilController', function () {
    var $componentController, $provide, $q, $log, $rootScope, controller, leaveRequest,
      AbsenceType, AbsenceTypeAPI, AbsencePeriodInstance, LeaveRequestInstance, TOILRequestInstance,
      balance;

    var date2016 = '01/12/2016';
    var date2016To = '02/12/2016'; // Must be greater than `date2016`

    beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _WorkPatternAPIMock_, _PublicHolidayAPIMock_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
      $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(['HR_settingsMock', function (_HRSettingsMock_) {
      $provide.value('HR_settings', _HRSettingsMock_);
    }]));

    beforeEach(inject(function (
      _$componentController_, _$q_, _$log_, _$rootScope_, _AbsenceType_, _AbsenceTypeAPI_, _AbsencePeriodInstance_,
      _LeaveRequestInstance_, _TOILRequestInstance_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      AbsencePeriodInstance = _AbsencePeriodInstance_;
      LeaveRequestInstance = _LeaveRequestInstance_;
      TOILRequestInstance = _TOILRequestInstance_;

      spyOn($log, 'debug');
      spyOn(AbsenceTypeAPI, 'calculateToilExpiryDate').and.callThrough();
      spyOn(AbsenceType, 'canExpire').and.callThrough();

      balance = {
        closing: 0,
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        }
      };
    }));

    describe('on initialize', function () {
      beforeEach(function () {
        leaveRequest = TOILRequestInstance.init();

        var params = compileComponent({
          leaveType: 'toil',
          request: leaveRequest
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();

        controller.request.type_id = params.selectedAbsenceType.id;
      });

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('has leave type as "toil"', function () {
        expect(controller.isLeaveType('toil')).toBeTruthy();
      });

      it('loads toil amounts', function () {
        expect(Object.keys(controller.toilAmounts).length).toBeGreaterThan(0);
      });

      it('defaults to a multiple day selection', function () {
        expect(controller.uiOptions.multipleDays).toBe(true);
      });

      describe('when multiple/single days mode changes', function () {
        describe('when the balance can but fails to be calculated', function () {
          beforeEach(function () {
            // This ensures the balance can be calculated
            controller.request.toil_to_accrue = 1;
            // While this ensures it fails to be calculated for some reason
            spyOn(controller, 'calculateBalanceChange').and.returnValue($q.reject());
            spyOn(controller, 'setDaysSelectionModeExtended').and.callThrough();
            controller.daysSelectionModeChangeHandler();
            $rootScope.$digest();
          });

          it('still performs the actions extended for TOIL', function () {
            expect(controller.setDaysSelectionModeExtended).toHaveBeenCalled();
          });
        });
      });

      describe('onDateChangeExtended()', function () {
        var promiseIsResolved = false;

        beforeEach(function () {
          // Resetting dates will make calculateToilExpiryDate() to reject
          controller.request.from_date = null;
          controller.request.to_date = null;
          controller.onDateChangeExtended().then(function () {
            promiseIsResolved = true;
          });
          $rootScope.$digest();
        });

        it('resolves disregarding of the result of calculateToilExpiryDate()', function () {
          expect(promiseIsResolved).toBeTruthy();
        });
      });

      describe('create', function () {
        describe('with selected duration and dates', function () {
          describe('when multiple days request', function () {
            beforeEach(function () {
              var toilAccrue = optionGroupMock.specificObject('hrleaveandabsences_toil_amounts', 'name', 'quarter_day');

              setTestDates(date2016, date2016To);
              controller.request.toilDurationHours = 1;
              controller.request.updateDuration();
              controller.request.toil_to_accrue = toilAccrue.value;

              $rootScope.$apply();
            });

            it('sets expiry date', function () {
              expect(controller.expiryDate).toEqual(absenceTypeData.calculateToilExpiryDate().values.toil_expiry_date);
            });

            it('calls calculateToilExpiryDate on AbsenceType', function () {
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[0]).toEqual(controller.request.type_id);
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[1]).toEqual(controller.request.to_date);
            });

            describe('when user changes number of days selected', function () {
              beforeEach(function () {
                controller.daysSelectionModeChangeHandler();
              });

              it('does not reset toil attributes', function () {
                expect(controller.request.toilDurationHours).not.toEqual('0');
                expect(controller.request.toilDurationMinutes).toEqual('0');
                expect(controller.request.toil_to_accrue).not.toEqual('');
              });
            });
          });

          describe('when single days request', function () {
            beforeEach(function () {
              controller.uiOptions.multipleDays = false;
              setTestDates(date2016);
            });

            it('calls calculateToilExpiryDate on AbsenceType', function () {
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[1]).toEqual(controller.request.from_date);
            });
          });
        });
      });

      describe('edit', function () {
        var toilRequest, absenceType;

        beforeEach(function () {
          toilRequest = TOILRequestInstance.init(leaveRequestData.findBy('request_type', 'toil'));
          toilRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

          compileComponent({
            leaveType: 'toil',
            mode: 'edit',
            request: toilRequest
          });
          spyOn(controller, 'performBalanceChangeCalculation').and.callThrough();

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();

          absenceType = _.find(controller.absenceTypes, function (absenceType) {
            return absenceType.id === controller.request.type_id;
          });
        });

        it('does not calculate balance yet', function () {
          expect(controller.performBalanceChangeCalculation).not.toHaveBeenCalled();
        });

        it('sets balance', function () {
          expect(controller.balance.opening).not.toBeLessThan(0);
        });

        it('sets absence types', function () {
          expect(absenceType.id).toEqual(toilRequest.type_id);
        });

        it('shows balance', function () {
          expect(controller.uiOptions.showBalance).toBeTruthy();
        });
      });
    });

    describe('respond', function () {
      describe('by manager', function () {
        var expiryDate, originalToilToAccrue, toilRequest;

        beforeEach(function () {
          expiryDate = '2017-12-31';
          toilRequest = TOILRequestInstance.init();
          toilRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          toilRequest.toil_expiry_date = expiryDate;

          var params = compileComponent({
            leaveType: 'toil',
            request: toilRequest,
            role: 'manager'
          });

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();
          controller.request.type_id = params.selectedAbsenceType.id;
          setTestDates(date2016, date2016To);
          $rootScope.$digest();

          expiryDate = new Date(controller.request.toil_expiry_date);
          originalToilToAccrue = optionGroupMock.specificObject('hrleaveandabsences_toil_amounts', 'name', 'quarter_day');
          controller.request.toil_to_accrue = originalToilToAccrue.value;
        });

        it('expiry date is set on ui', function () {
          expect(controller.uiOptions.expiryDate).toEqual(expiryDate);
        });

        describe('and changes expiry date', function () {
          var oldExpiryDate, newExpiryDate;

          beforeEach(function () {
            oldExpiryDate = controller.request.toil_expiry_date;
            controller.uiOptions.expiryDate = new Date();
            newExpiryDate = controller.convertDateToServerFormat(controller.uiOptions.expiryDate);
            controller.updateExpiryDate();
          });

          it('new expiry date is not same as old expiry date', function () {
            expect(oldExpiryDate).not.toEqual(controller.request.toil_expiry_date);
          });

          it('sets new expiry date', function () {
            expect(controller.request.toil_expiry_date).toEqual(newExpiryDate);
          });

          describe('and staff edits open request', function () {
            beforeEach(function () {
              compileComponent({
                leaveType: 'toil',
                mode: 'edit',
                request: controller.request
              });

              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();

              controller.uiOptions.expiryDate = oldExpiryDate;

              controller.updateExpiryDate();
            });

            it('has role as "staff"', function () {
              expect(controller.isRole('staff')).toBeTruthy();
            });

            it('has expired date set by manager', function () {
              expect(controller.request.toil_expiry_date).toEqual(oldExpiryDate);
            });

            it('has toil amount set by manager', function () {
              expect(controller.request.toil_to_accrue).toEqual(originalToilToAccrue.value);
            });
          });

          describe('clears expiry date', function () {
            beforeEach(function () {
              controller.clearExpiryDate();
            });

            it('resets expiry date in both UI and request', function () {
              expect(controller.request.toil_expiry_date).toBeFalsy();
              expect(controller.uiOptions.expiryDate).toBeFalsy();
            });
          });
        });
      });
    });

    describe('when TOIL Request does not expire', function () {
      beforeEach(function () {
        AbsenceType.canExpire.and.returnValue($q.resolve(false));
        compileComponent({
          leaveType: 'toil',
          request: controller.request
        });

        $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
        $rootScope.$digest();
      });

      it('should set requestCanExpire to false', function () {
        expect(controller.requestCanExpire).toBe(false);
      });

      describe('when request date changes', function () {
        beforeEach(function () {
          spyOn(AbsenceType, 'calculateToilExpiryDate');
          controller.request.to_date = new Date();
          $rootScope.$digest();
        });

        it('should not calculate the expiry date field', function () {
          expect(AbsenceType.calculateToilExpiryDate).not.toHaveBeenCalled();
        });

        it('should set expiry date to false', function () {
          expect(controller.request.toil_expiry_date).toBe(false);
        });
      });
    });

    /**
    * Appends default values to the controller initialiation.
    *
    * @param {Object} params - the object to wich defaults will be appented to.
    * properties and defaults:
    * - {Array} absencePeriods - a list of absence periods. Defaults to all absence periods.
    * - {Array} absenceTypes - a list of absence types. Defaults to all absence types.
    * - {Object} balance - the request balance. Defaults to the globally defined balance.
    * - {JasmineSpy} checkSubmitConditions - a spy to execute the checkSubmitConditions callback.
    * - {JasmineSpy} isLeaveStatus - a spy to execute the isLeaveStatus callback.
    * - {String} leaveType - the leave absence type. Options are "leave", "sick", "toil". Defaults to "leave".
    * - {Object} period - the currently selected period. Defaults to first period.
    * - {Object} selectedAbsenceType - the selected absence type. Defaults to the first absence type, and sets remainder value to 0.
    * - {Object} request - The leave request data. Defaults to an empty leave request.
    * - {JasmineSpy} isMode - a isMode spy function.
    * - {JasmineSpy} isRole - a isRole spy function.
    */
    function addDefaultComponentParams (params) {
      addSpyParams(params);

      var defaultParams = {
        absencePeriods: absencePeriodData.all().values.map(function (period) {
          return AbsencePeriodInstance.init(period);
        }),
        absenceTypes: absenceTypeData.all().values,
        balance: balance, // balance is set globally
        checkSubmitConditions: params.checkSubmitConditions,
        isLeaveStatus: params.isLeaveStatus,
        leaveType: 'leave',
        period: absencePeriodData.all().values[0],
        selectedAbsenceType: _.assign(absenceTypeData.all().values[0], {
          remainder: 0
        }),
        request: LeaveRequestInstance.init(),
        isMode: params.isMode,
        isRole: params.isRole
      };

      _.defaults(params, defaultParams);
    }

    /**
     * Appends default spy functions to the params object.
     *
     * @param {Object} params - the object which spy functions will be appened to.
     */
    function addSpyParams (params) {
      var defaultParams = {
        mode: 'create',
        role: 'staff'
      };

      _.defaults(params, defaultParams);

      params.isMode = jasmine.createSpy('isMode')
        .and.callFake(function (mode) {
          return mode === params.mode;
        });

      params.isRole = jasmine.createSpy('isRole')
        .and.callFake(function (role) {
          return role === params.role;
        });

      params.checkSubmitConditions = jasmine.createSpy('checkSubmitConditions');
      params.isLeaveStatus = jasmine.createSpy('isLeaveStatus')
        .and.callFake(function (statusName) {
          return getStatusValueFromName(statusName) === params.request.status_id;
        });
    }

    /**
     * Compiles and initializes the component's controller. It returns the
     * parameters used to initialize the controller plus default parameter
     * values.
     *
     * @param {Object} params - the values to initialize the component. Defaults
     * to an empty object.
     *
     * @return {Object}
     */
    function compileComponent (params) {
      params = params || {};

      addDefaultComponentParams(params);

      controller = $componentController(
        'leaveRequestPopupDetailsTab',
        null,
        params
      );

      $rootScope.$digest();

      return params;
    }

    /**
     * sets from and/or to dates
     * @param {String} from date set if passed
     * @param {String} to date set if passed
     */
    function setTestDates (from, to) {
      if (from) {
        controller.uiOptions.fromDate = getUTCDate(from);
        controller.dateChangeHandler('from');
        $rootScope.$digest();
      }

      if (to) {
        controller.uiOptions.toDate = getUTCDate(to);
        controller.dateChangeHandler('to');
        $rootScope.$digest();
      }
    }

    /**
     * Returns a UTC Date object from a string.
     *
     * @param {String} date - the date to convert to UTC Date object.
     * @return {Date}
     */
    function getUTCDate (date) {
      var now = new Date(date);
      return new Date(now.getTime() + now.getTimezoneOffset() * 60000);
    }

    /**
     * Returns the id for a specific status by filtering using the status name.
     *
     * @param {String} statusName - The name of the status to filter by.
     * @return {Number}
     */
    function getStatusValueFromName (statusName) {
      var status = optionGroupMock.specificObject(
        'hrleaveandabsences_leave_request_status',
        'name',
        statusName
      );

      return status.value;
    }
  });
});
