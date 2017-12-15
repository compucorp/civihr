/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'mocks/data/absence-period.data',
  'mocks/data/absence-type.data',
  'mocks/data/leave-request.data',
  'mocks/data/option-group.data',
  'mocks/helpers/helper',
  'mocks/apis/option-group-api-mock',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, absencePeriodData, absenceTypeData, leaveRequestData, optionGroupMock, helper) {
  'use strict';

  describe('leaveRequestPopupDetailsTab', function () {
    var $componentController, $provide, $q, $log, $rootScope, controller, sharedSettings, LeaveRequestAPI,
      AbsenceType, leaveRequest, AbsenceTypeAPI, AbsencePeriodInstance, LeaveRequestInstance, SicknessRequestInstance,
      TOILRequestInstance, OptionGroup, OptionGroupAPIMock, balance, selectedAbsenceType, WorkPatternAPI, EntitlementAPI;

    var date2013 = '02/02/2013';
    var date2016 = '01/12/2016';
    var date2016To = '02/12/2016';
    var date2017 = '01/02/2017';
    var date2017To = '02/02/2017';
    var dateServer2017 = '2017-02-02';

    beforeEach(module('common.mocks', 'leave-absences.templates',
    'leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _WorkPatternAPIMock_, _PublicHolidayAPIMock_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
      $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(['HR_settingsMock', 'shared-settings', function (_HRSettingsMock_, _sharedSettings_) {
      $provide.value('HR_settings', _HRSettingsMock_);
      sharedSettings = _sharedSettings_;
    }]));

    beforeEach(inject(function (
      _$componentController_, _$q_, _$log_, _$rootScope_, _AbsenceType_, _AbsenceTypeAPI_, _AbsencePeriodInstance_,
      _LeaveRequestInstance_, _TOILRequestInstance_, _SicknessRequestInstance_, _OptionGroup_, _OptionGroupAPIMock_,
      _LeaveRequestAPI_, _WorkPatternAPI_, _EntitlementAPI_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      AbsencePeriodInstance = _AbsencePeriodInstance_;
      LeaveRequestInstance = _LeaveRequestInstance_;
      SicknessRequestInstance = _SicknessRequestInstance_;
      TOILRequestInstance = _TOILRequestInstance_;
      LeaveRequestAPI = _LeaveRequestAPI_;
      WorkPatternAPI = _WorkPatternAPI_;
      EntitlementAPI = _EntitlementAPI_;
      OptionGroupAPIMock = _OptionGroupAPIMock_;
      OptionGroup = _OptionGroup_;

      spyOn($log, 'debug');
      spyOn(LeaveRequestAPI, 'calculateBalanceChange').and.callThrough();
      spyOn(LeaveRequestAPI, 'getBalanceChangeBreakdown').and.callThrough();
      spyOn(AbsenceTypeAPI, 'calculateToilExpiryDate').and.callThrough();
      spyOn(AbsenceType, 'canExpire').and.callThrough();
      spyOn(AbsenceType, 'loadCalculationUnits').and.callThrough();
      spyOn(EntitlementAPI, 'all').and.callThrough();
      spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
      spyOn(OptionGroup, 'valuesOf').and.callFake(function (name) {
        return OptionGroupAPIMock.valuesOf(name);
      });

      balance = {
        closing: 0,
        opening: 0,
        change: {
          amount: 0,
          breakdown: []
        }
      };
    }));

    describe('when request type is Leave', function () {
      describe('on initialize', function () {
        beforeEach(function () {
          selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {remainder: 0});

          compileComponent();

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();

          controller.request.type_id = selectedAbsenceType.id;
        });

        it('is initialized', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('has leave type as "leave"', function () {
          expect(controller.isLeaveType('leave')).toBeTruthy();
        });

        describe('initChildController()', function () {
          it('has days of work pattern loaded', function () {
            expect(controller.calendar).toBeDefined();
            expect(controller.calendar.days).toBeDefined();
          });

          it('has day types loaded', function () {
            expect(controller.requestDayTypes).toBeDefined();
          });

          it('has no dates selected', function () {
            expect(controller.uiOptions.fromDate).not.toBeDefined();
            expect(controller.uiOptions.toDate).not.toBeDefined();
          });

          it('defaults to a multiple day selection', function () {
            expect(controller.uiOptions.multipleDays).toBe(true);
          });

          it('has no day types selected', function () {
            expect(controller.uiOptions.selectedFromType).not.toBeDefined();
            expect(controller.uiOptions.selectedToType).not.toBeDefined();
          });

          it('does not show balance', function () {
            expect(controller.uiOptions.showBalance).toBeFalsy();
            expect(controller.balance.opening).toEqual(jasmine.any(Number));
          });

          it('has balance change hidden', function () {
            expect(controller.uiOptions.isChangeExpanded).toBeFalsy();
          });

          it('has nil total items for balance change pagination', function () {
            expect(controller.pagination.totalItems).toEqual(0);
          });

          describe('multiple days', function () {
            it('is selected by default', function () {
              expect(controller.uiOptions.multipleDays).toBeTruthy();
            });
          });
        });

        describe('isNotWorkingDay()', function () {
          it('checks if not a working day by the given day type', function () {
            expect(controller.isNotWorkingDay('weekend')).toBeTruthy();
            expect(controller.isNotWorkingDay('non_working_day')).toBeTruthy();
            expect(controller.isNotWorkingDay('public_holiday')).toBeTruthy();
            expect(controller.isNotWorkingDay('christmas_eve')).toBeFalsy();
          });
        });

        describe('right after from date is selected', function () {
          it('flushes time deductions immediately');
        });

        describe('after from date is selected and it does not belong to any absence period', function () {
          beforeEach(function () {
            spyOn($rootScope, '$broadcast');
            controller.uiOptions.fromDate = getUTCDate('01/01/1800');
            controller.dateChangeHandler('from');
            $rootScope.$digest();
          });

          it('throws error', function () {
            expect($rootScope.$broadcast).toHaveBeenCalledWith('LeaveRequestPopup::handleError',
              [ 'Please change date as it is not in any absence period' ]);
          });
        });

        describe('after from date is selected', function () {
          var fromDate;

          beforeEach(function () {
            setTestDates(date2016);
            fromDate = moment(controller.uiOptions.fromDate).format(sharedSettings.serverDateFormat);
          });

          it('has balance change defined', function () {
            expect(controller.balance).toEqual(jasmine.any(Object));
            expect(controller.balance.opening).toEqual(jasmine.any(Number));
            expect(controller.balance.change).toEqual(jasmine.any(Object));
            expect(controller.balance.closing).toEqual(jasmine.any(Number));
          });

          it('has from date set', function () {
            expect(controller.request.from_date).toEqual(fromDate);
          });

          it('selects first day type', function () {
            expect(controller.request.from_date_type).toEqual('1');
          });

          describe('and from date is weekend', function () {
            var testDate;

            beforeEach(function () {
              testDate = helper.getDate('weekend');
              setTestDates(testDate.date);
            });

            it('sets weekend day type', function () {
              expect(controller.requestFromDayTypes[0].label).toEqual('Weekend');
            });
          });

          describe('and from date is non working day', function () {
            var testDate;

            beforeEach(function () {
              testDate = helper.getDate('non_working_day');
              setTestDates(testDate.date);
            });

            it('sets non_working_day day type', function () {
              expect(controller.requestFromDayTypes[0].label).toEqual('Non Working Day');
            });
          });

          describe('and from date is working day', function () {
            var testDate;

            beforeEach(function () {
              testDate = helper.getDate('working_day');
              setTestDates(testDate.date);
            });

            it('sets non_working_day day type', function () {
              expect(controller.requestFromDayTypes.length).toEqual(3);
            });
          });
        });

        describe('after to date is selected', function () {
          var toDate;

          beforeEach(function () {
            setTestDates(date2016, date2016To);
            toDate = moment(controller.uiOptions.toDate).format(sharedSettings.serverDateFormat);
          });

          it('sets to date', function () {
            expect(controller.request.to_date).toEqual(toDate);
          });

          it('select first day type', function () {
            expect(controller.request.to_date_type).toEqual('1');
          });
        });

        describe('from and to dates are selected', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016To);
          });

          it('does show balance change', function () {
            expect(controller.uiOptions.showBalance).toBeTruthy();
          });
        });

        describe('leave absence types', function () {
          describe('on change selection', function () {
            var beforeChangeAbsenceType, afterChangeAbsenceType;

            beforeEach(function () {
              beforeChangeAbsenceType = controller.absenceTypes[0];
              controller.request.type_id = controller.absenceTypes[1].id;

              $rootScope.$broadcast('LeaveRequestPopup::absenceTypeChanged');

              afterChangeAbsenceType = controller.absenceTypes[1];

              $rootScope.$digest();
            });

            it('selects another absence type', function () {
              expect(beforeChangeAbsenceType.id).not.toEqual(afterChangeAbsenceType.id);
            });

            it('updates balance', function () {
              expect(controller.balance.opening).toEqual(afterChangeAbsenceType.remainder);
            });
          });
        });

        describe('when days mode is changed', function () {
          beforeEach(function () {
            controller.daysSelectionModeChangeHandler();
            $rootScope.$digest();
          });

          it('flushes "to" date', function () {
            expect(controller.uiOptions.toDate).toBe(null);
          });

          it('flushes "to" date types', function () {
            expect(controller['requestToDayTypes'].length).toBe(0);
          });

          it('flushes "to" date selected type', function () {
            expect(controller.uiOptions.selectedToType).not.toBeDefined();
          });

          it('resets balance', function () {
            // we expect balance change to be 0 because "from" and "to" dates are equal in a single day mode
            expect(controller.balance.change.amount).toEqual(0);
            // if balance change amount is 0 we expect closing balance be equal to opening balance
            expect(controller.balance.closing).toEqual(controller.balance.opening);
          });

          it('shows no balance', function () {
            expect(controller.uiOptions.showBalance).toBeFalsy();
          });

          describe('when switching to multiple day mode', function () {
            beforeEach(function () {
              controller.uiOptions.multipleDays = true;

              controller.daysSelectionModeChangeHandler();
              $rootScope.$digest();
            });

            it('calculates balance change', function () {
              expect(LeaveRequestAPI.calculateBalanceChange).not.toHaveBeenCalled();
            });

            describe('after from date is selected', function () {
              beforeEach(function () {
                setTestDates(date2016, date2016To);
              });

              it('sets from and to dates', function () {
                expect(controller.request.from_date).not.toBeNull();
                expect(controller.request.to_date).not.toBeNull();
              });

              it('shows balance', function () {
                expect(controller.uiOptions.showBalance).toBeTruthy();
              });
            });
          });
        });

        describe('calendar', function () {
          describe('when from date is selected', function () {
            beforeEach(function () {
              setTestDates(date2016);
            });

            it('sets from date', function () {
              expect(moment(controller.request.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            });
          });

          describe('when to date is selected', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016To);
            });

            it('sets to date', function () {
              expect(moment(controller.request.to_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            });
          });
        });

        describe('day types', function () {
          describe('on change selection', function () {
            var expectedDayType;

            beforeEach(function () {
              expectedDayType = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'value', '1');
              setTestDates(null, date2016);
            });

            it('selects to date type', function () {
              expect(controller.request.to_date_type).toEqual(expectedDayType);
            });
          });

          describe('when from and to are selected', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016To);
            });

            it('calculates balance change', function () {
              expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
            });
          });
        });

        describe('calculate balance', function () {
          describe('when day type changed', function () {
            describe('for single day', function () {
              beforeEach(function () {
                // select half_day_am to get single day mock data
                controller.request.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'half_day_am');
                controller.dateTypeChangeHandler();
                $rootScope.$digest();
              });

              it('updates balance', function () {
                expect(controller.balance.change.amount).toEqual(jasmine.any(Number));
              });

              it('updates closing balance', function () {
                expect(controller.balance.closing).toEqual(jasmine.any(Number));
              });
            });

            describe('for multiple days', function () {
              beforeEach(function () {
                controller.uiOptions.multipleDays = true;
                // select all_day to get multiple day mock data
                setTestDates(date2016, date2016To);

                controller.request.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');

                controller.dateTypeChangeHandler();
                $rootScope.$digest();
              });

              it('updates change amount', function () {
                expect(controller.balance.change.amount).toEqual(-2);
              });

              it('updates closing balance', function () {
                expect(controller.balance.closing).toEqual(jasmine.any(Number));
              });
            });
          });

          describe('when balance change is expanded during pagination', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016To);
            });

            it('paginates by 7 items', function () {
              expect(controller.pagination.numPerPage).toEqual(7);
            });

            it('selects default page', function () {
              expect(controller.pagination.currentPage).toEqual(1);
            });

            it('sets totalItems', function () {
              expect(controller.pagination.totalItems).toBeGreaterThan(0);
            });

            describe('when page selection changes', function () {
              var beforeFilteredItems;

              beforeEach(function () {
                beforeFilteredItems = controller.pagination.filteredbreakdown;
                controller.pagination.currentPage = 2;
                controller.pagination.pageChanged();
              });

              it('changes current page', function () {
                expect(controller.pagination.currentPage).not.toEqual(1);
              });

              it('changes filtered data', function () {
                expect(controller.pagination.filteredbreakdown[0]).not.toEqual(beforeFilteredItems[0]);
              });
            });
          });
        });

        describe('when leave absence type has "hours" calculation unit', function () {
          describe('on initialise', function () {
            beforeEach(function () {
              selectedAbsenceType.calculation_unit_name = 'hours';

              compileComponent();

              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();

              controller.request.type_id = selectedAbsenceType.id;
            });

            afterEach(function () {
              selectedAbsenceType.calculation_unit_name = 'days';
            });

            it('has a storage for time selectors', function () {
              ['from', 'to'].forEach(function (type) {
                expect(controller.uiOptions.times[type].time).toBeDefined();
                expect(controller.uiOptions.times[type].amount).toBeDefined();
                expect(controller.uiOptions.times[type].maxAmount).toBeDefined();
                expect(controller.uiOptions.times[type].disabled).toBeDefined();
              });
            });

            it('defaults data selection to a single day', function () {
              expect(controller.uiOptions.multipleDays).toBeFalsy();
            });

            describe('after from date is selected', function () {
              var timeFromObject, request, workDayMock;

              beforeEach(function () {
                timeFromObject = controller.uiOptions.times.from;
                request = controller.request;
                workDayMock = leaveRequestData.workDayForDate().values;

                setTestDates(date2016);
                $rootScope.$digest();
              });

              it('turns loading indicator off', function () {
                expect(timeFromObject.loading).toBeFalsy();
              });

              it('sets minimum timepicker option', function () {
                expect(timeFromObject.min).toBe(workDayMock.time_from);
              });

              it('sets maximum timepicker option', function () {
                expect(timeFromObject.max).toBe(workDayMock.time_to);
              });

              it('pre-sets default timepicker option same as *minimum*', function () {
                expect(timeFromObject.time).toBe(timeFromObject.min);
              });

              it('allows user to select "from" time', function () {
                expect(timeFromObject.disabled).toBeFalsy();
              });

              it('sets the maximum deduction amount', function () {
                expect(timeFromObject.maxAmount).toBe(workDayMock.number_of_hours);
              });

              it('sets the default deduction amount same as maximum', function () {
                expect(timeFromObject.amount).toBe(timeFromObject.maxAmount);
              });

              it('sets the "from" date to request in a date+time format', function () {
                expect(request.from_date.length).toBe('YYYY-MM-DD hh:mm'.length);
              });

              describe('after to date is selected', function () {
                var timeToObject;

                beforeEach(function () {
                  timeToObject = controller.uiOptions.times.to;

                  setTestDates(date2016, date2017);
                });

                it('turns loading indicator off', function () {
                  expect(timeToObject.loading).toBeFalsy();
                });

                it('sets minimum timepicker option', function () {
                  expect(timeToObject.min).toBe(workDayMock.time_from);
                });

                it('sets maximum timepicker option', function () {
                  expect(timeToObject.max).toBe(workDayMock.time_to);
                });

                it('pre-sets default timepicker option same as *maximum*', function () {
                  expect(timeToObject.time).toBe(timeToObject.max);
                });

                it('allows user to select "from" time', function () {
                  expect(timeToObject.disabled).toBeFalsy();
                });

                it('sets the maximum deduction amount', function () {
                  expect(timeToObject.maxAmount).toBe(workDayMock.number_of_hours);
                });

                it('sets the default deduction amount same as maximum', function () {
                  expect(timeToObject.amount).toBe(timeToObject.maxAmount);
                });

                it('sets the "from" date to request in a date+time format', function () {
                  expect(request.to_date.length).toBe('YYYY-MM-DD hh:mm'.length);
                });

                it('shows the balance', function () {
                  expect(controller.uiOptions.showBalance).toBeTruthy();
                });

                describe('if work day info cannot be retrieved', function () {
                  beforeEach(function () {
                    spyOn($rootScope, '$broadcast');
                    spyOn(request, 'getWorkDayForDate').and.returnValue($q.reject());
                    setTestDates(date2016, date2017);
                  });

                  it('flushes and disables time and deduction fields', function () {
                    expect(timeToObject.disabled).toBeTruthy();
                    expect(timeToObject.time).toBe('');
                    expect(timeToObject.amount).toBe('0');
                  });

                  it('shows the error', function () {
                    expect($rootScope.$broadcast).toHaveBeenCalledWith(
                      'LeaveRequestPopup::handleError', jasmine.any(Array));
                  });
                });

                describe('and from date is greater than to date', function () {
                  var timesTo;

                  beforeEach(function () {
                    timesTo = controller.uiOptions.times.to;
                    controller.uiOptions.multipleDays = true;

                    setTestDates(null, '10/12/2016');
                    setTestDates('11/12/2016');
                  });

                  it('resets "to" times and durations', function () {
                    expect(timesTo.time).toBe('');
                    expect(timesTo.min).toBe('00:00');
                    expect(timesTo.max).toBe('00:00');
                    expect(timesTo.amount).toBe('0');
                    expect(timesTo.maxAmount).toBe('0');
                    expect(timesTo.disabled).toBe(false);
                    expect(timesTo.loading).toBe(false);
                  });
                });
              });
            });

            describe('when absence period is changed', function () {
              beforeEach(function () {
                $rootScope.$broadcast('LeaveRequestPopup::absencePeriodChanged');
                $rootScope.$digest();
              });

              it('sets data selection to a single day', function () {
                expect(controller.uiOptions.multipleDays).toBeFalsy();
              });
            });
          });

          describe('when user edits the request', function () {
            var leaveRequest;
            var fromDeduction = '1.5';
            var toDeduction = '4.75';

            beforeEach(function () {
              var status = optionGroupMock.specificValue(
                'hrleaveandabsences_leave_request_status', 'value', '3');

              leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));
              selectedAbsenceType.calculation_unit_name = 'hours';
              leaveRequest.contact_id = '' + CRM.vars.leaveAndAbsences.contactId;
              leaveRequest.type_id = selectedAbsenceType.id;
              leaveRequest.from_date_amount = fromDeduction;
              leaveRequest.to_date_amount = toDeduction;

              compileComponent({
                mode: 'edit',
                request: leaveRequest
              });
              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();
            });

            afterEach(function () {
              selectedAbsenceType.calculation_unit_name = 'days';
            });

            it('sets time and deduction', function () {
              expect(controller.uiOptions.times.from.time).toBe(moment(leaveRequest.from_date).format('HH:mm'));
              expect(controller.uiOptions.times.to.time).toBe(moment(leaveRequest.to_date).format('HH:mm'));
              expect(controller.uiOptions.times.from.amount).toBe(leaveRequest.from_date_amount);
              expect(controller.uiOptions.times.to.amount).toBe(leaveRequest.to_date_amount);
            });

            it('does not recalculate the balance', function () {
              expect(LeaveRequestAPI.calculateBalanceChange).not.toHaveBeenCalled();
            });

            describe('when deduction is greater than allowed', function () {
              var maxDeduction;

              beforeEach(function () {
                maxDeduction = (Math.min(controller.request.from_date_amount, controller.request.to_date_amount) - 0.001).toString();

                spyOn(controller.request, 'getWorkDayForDate').and
                  .returnValue($q.resolve({ number_of_hours: maxDeduction }));
                compileComponent({
                  mode: 'edit',
                  request: leaveRequest
                });
                $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
                $rootScope.$digest();
              });

              it('resets both "from" and "to" to their maximum allowed values', function () {
                expect(controller.request.from_date_amount).toBe(maxDeduction);
                expect(controller.request.to_date_amount).toBe(maxDeduction);
              });
            });
          });
        });
      });

      describe('when absence period is changed', function () {
        var previousDateType = '<previous-value>';

        beforeEach(function () {
          var params = compileComponent({
            mode: 'create',
            selectedAbsenceType: selectedAbsenceType
          });

          controller.request.from_date_type = previousDateType;
          controller.request.to_date_type = previousDateType;

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();

          controller.request.type_id = params.selectedAbsenceType.id;
        });

        describe('for multiple days', function () {
          describe('before from date is selected', function () {
            it('disables to date and to type', function () {
              expect(controller.request.from_date).toBeFalsy();
            });
          });

          describe('and after from date is selected', function () {
            beforeEach(function () {
              setTestDates(date2017);
            });

            it('enables to date and to type', function () {
              expect(controller.request.from_date).toBeTruthy();
            });

            it('checks if date is in any absence period without errors', function () {
              expect(controller.errors.length).toBe(0);
            });

            it('updates calendar', function () {
              expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
            });

            it('does not show balance', function () {
              expect(controller.uiOptions.showBalance).toBeFalsy();
            });

            it('updates "from" day type in the request', function () {
              expect(controller.request.from_date_type).not.toBe(previousDateType);
            });

            describe('from available absence period', function () {
              var oldPeriodId;

              beforeEach(function () {
                controller.uiOptions.toDate = null;
                oldPeriodId = controller.period.id;
                spyOn($rootScope, '$broadcast').and.callThrough();
                setTestDates(date2016);
              });

              it('notifies the parent controller that the period has been changed', function () {
                expect($rootScope.$broadcast).toHaveBeenCalledWith('LeaveRequestPopup::absencePeriodChanged');
              });

              it('changes absence period', function () {
                expect(controller.period.id).not.toEqual(oldPeriodId);
              });

              it('sets min and max to date', function () {
                expect(controller.uiOptions.date.to.options.minDate).not.toBeNull();
                expect(controller.uiOptions.date.to.options.maxDate).not.toBeNull();
              });

              it('does not show balance', function () {
                expect(controller.uiOptions.showBalance).toBeFalsy();
              });

              it('resets to date', function () {
                expect(controller.request.to_date).toBeNull();
              });
            });

            describe('from unavailable absence period', function () {
              beforeEach(function () {
                setTestDates(date2013);
              });

              it('shows error', function () {
                expect(controller.errors).toEqual(jasmine.any(Array));
              });
            });

            describe('and to date is selected', function () {
              beforeEach(function () {
                setTestDates(date2016, date2016To);
              });

              it('selects date from selected absence period without errors', function () {
                expect(controller.errors.length).toBe(0);
              });

              it('updates balance', function () {
                expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
              });

              it('shows balance', function () {
                expect(controller.uiOptions.showBalance).toBeTruthy();
              });

              it('updates "to" day type in the request', function () {
                expect(controller.request.from_date_type).not.toBe(previousDateType);
              });
            });

            describe('and from date is changed after to date', function () {
              var from, to, minDate;

              beforeEach(function () {
                setTestDates(date2016);
                minDate = moment(getUTCDate(date2016)).add(1, 'd').toDate();
              });

              describe('when parent controller responds back', function () {
                var previousMultipleDaysOptionValue;
                var differentRemainder = '<any-different-remainer>';

                beforeEach(function () {
                  var absenceTypesWithBalances = _.cloneDeep(controller.absenceTypes);

                  previousMultipleDaysOptionValue = controller.uiOptions.multipleDays;
                  controller.selectedAbsenceType.remainder = differentRemainder;

                  $rootScope.$emit('LeaveRequestPopup::absencePeriodBalancesUpdated', absenceTypesWithBalances);
                  $rootScope.$digest();
                });

                it('updates selected absence type remainder', function () {
                  expect(controller.selectedAbsenceType.remainder).not.toBe(differentRemainder);
                });

                it('does not affect the "single/multiple days" option', function () {
                  expect(controller.uiOptions.multipleDays).toBe(previousMultipleDaysOptionValue);
                });

                it('sets min date to from date', function () {
                  expect(controller.uiOptions.date.to.options.minDate).toEqual(minDate);
                });

                it('sets init date to from date', function () {
                  expect(controller.uiOptions.date.to.options.initDate).toEqual(minDate);
                });

                describe('and from date is less than to date', function () {
                  beforeEach(function () {
                    from = '9/12/2016';
                    to = '10/12/2016';

                    setTestDates(null, to);
                    setTestDates(from);
                  });

                  it('does not reset to date to equal from date', function () {
                    expect(controller.request.to_date).not.toEqual(controller.request.from_date);
                  });
                });

                describe('and from date is greater than to date', function () {
                  beforeEach(function () {
                    from = '11/12/2016';
                    to = '10/12/2016';

                    setTestDates(null, to);
                    setTestDates(from);
                  });

                  it('resets To date', function () {
                    expect(controller.request.to_date).toEqual(null);
                  });

                  it('resets To day types', function () {
                    expect(controller.requestToDayTypes).toEqual([]);
                  });

                  it('does not show day types being loaded', function () {
                    expect(controller.loading.ToDayTypes).toBeFalsy();
                  });
                });
              });
            });

            describe('when setting "from" date that matches earlier absence period', function () {
              beforeEach(function () {
                var absenceTypesWithBalances = _.cloneDeep(controller.absenceTypes);

                setTestDates(date2017, date2017To);
                setTestDates(date2016);
                $rootScope.$broadcast('LeaveRequestPopup::absencePeriodBalancesUpdated', absenceTypesWithBalances);
              });

              it('resets "to" date', function () {
                expect(controller.request.to_date).toEqual(null);
              });

              it('resets "to" day types', function () {
                expect(controller.requestToDayTypes).toEqual([]);
              });

              it('does not show "to" day types being loaded', function () {
                expect(controller.loading.ToDayTypes).toBeFalsy();
              });
            });
          });
        });
      });

      describe('when user edits leave request', function () {
        describe('without comments', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));
            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

            var params = compileComponent({
              mode: 'edit',
              request: leaveRequest
            });

            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();

            controller.request.type_id = params.selectedAbsenceType.id;
          });

          describe('on initialization', function () {
            var waitingApprovalStatus;

            beforeEach(function () {
              waitingApprovalStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
            });

            it('sets all leaverequest values', function () {
              expect(controller.request.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
              expect(controller.request.type_id).toEqual('1');
              expect(controller.request.status_id).toEqual(waitingApprovalStatus.value);
              expect(controller.request.from_date).toEqual('2016-11-23');
              expect(controller.request.from_date_type).toEqual('1');
              expect(controller.request.to_date).toEqual('2016-11-28');
              expect(controller.request.to_date_type).toEqual('1');
            });

            it('retrieves original balance breakdown', function () {
              expect(LeaveRequestAPI.getBalanceChangeBreakdown).toHaveBeenCalled();
              expect(controller.loading.balanceChange).toBe(false);
            });

            it('does not recalculate the balance', function () {
              expect(LeaveRequestAPI.calculateBalanceChange).not.toHaveBeenCalled();
            });

            it('shows balance', function () {
              expect(controller.uiOptions.showBalance).toBeTruthy();
            });

            it('loads day types', function () {
              expect(controller.requestFromDayTypes).toBeDefined();
              expect(controller.requestToDayTypes).toBeDefined();
            });
          });

          describe('user selects same from and to date', function () {
            beforeEach(function () {
              var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
              var leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));

              leaveRequest.from_date = leaveRequest.to_date = dateServer2017;
              leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

              compileComponent({
                mode: 'edit',
                request: leaveRequest,
                selectedAbsenceType: selectedAbsenceType
              });

              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();

              controller.request.type_id = selectedAbsenceType.id;
            });

            it('selects single day', function () {
              expect(controller.uiOptions.multipleDays).toBeFalsy();
            });
          });
        });
      });

      describe('in view mode', function () {
        var leaveRequest;

        beforeEach(function () {
          var approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');

          leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', approvalStatus));
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

          compileComponent({
            mode: 'view',
            request: leaveRequest,
            selectedAbsenceType: selectedAbsenceType
          });

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();

          controller.request.type_id = selectedAbsenceType.id;
        });

        it('retrieves original balance breakdown', function () {
          expect(LeaveRequestAPI.getBalanceChangeBreakdown).toHaveBeenCalled();
          expect(controller.loading.balanceChange).toBe(false);
        });

        it('sets mode to view', function () {
          expect(controller.isMode('view')).toBeTruthy();
        });

        it('sets contact id', function () {
          expect(controller.request.contact_id).toEqual(leaveRequest.contact_id);
        });
      });
    });

    describe('when request type is Sick', function () {
      describe('on initialize', function () {
        beforeEach(function () {
          selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {remainder: 0});
          leaveRequest = SicknessRequestInstance.init();

          compileComponent({
            leaveType: 'sick',
            request: leaveRequest,
            selectedAbsenceType: selectedAbsenceType
          });

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();

          controller.request.type_id = selectedAbsenceType.id;
        });

        it('is initialized', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('has leave type as "sickness"', function () {
          expect(controller.isLeaveType('sickness')).toBeTruthy();
        });

        describe('initChildController()', function () {
          it('loads reasons option types', function () {
            expect(Object.keys(controller.sicknessReasons).length).toBeGreaterThan(0);
          });

          it('loads documents option types', function () {
            expect(controller.sicknessDocumentTypes.length).toBeGreaterThan(0);
          });
        });

        describe('isDocumentInRequest()', function () {
          var documents = optionGroupMock.getCollection('hrleaveandabsences_leave_request_required_document');

          it('checks if the document is in the request', function () {
            expect(controller.isDocumentInRequest(documents[0].value)).toBeTruthy();
            expect(controller.isDocumentInRequest('non-existing-document')).toBeFalsy();
          });
        });

        describe('with selected reason', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016To);
            setReason();
          });

          describe('when user changes number of days selected', function () {
            beforeEach(function () {
              controller.daysSelectionModeChangeHandler();
            });

            it('does not reset sickness reason', function () {
              expect(controller.request.sickness_reason).not.toBeNull();
            });
          });
        });

        describe('open sickness request in edit mode', function () {
          var sicknessRequest;

          beforeEach(function () {
            sicknessRequest = SicknessRequestInstance.init(leaveRequestData.findBy('request_type', 'sickness'));
            sicknessRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            sicknessRequest.sickness_required_documents = '1,2';
            sicknessRequest.status_id = optionGroupMock.specificValue(
              'hrleaveandabsences_leave_request_status', 'value', '3');

            compileComponent({
              leaveType: 'sick',
              mode: 'edit',
              request: sicknessRequest,
              selectedAbsenceType: selectedAbsenceType
            });

            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();
          });

          it('sets edit mode', function () {
            expect(controller.isMode('edit')).toBeTruthy();
          });

          it('does show balance', function () {
            expect(controller.uiOptions.showBalance).toBeTruthy();
          });

          describe('when request states multiple days', function () {
            beforeEach(function () {
              compileComponent({
                mode: 'edit',
                leaveType: 'sick',
                request: sicknessRequest,
                selectedAbsenceType: selectedAbsenceType
              });
              setTestDates(date2016, date2017);
              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();
            });

            it('shows multiple days', function () {
              expect(controller.uiOptions.multipleDays).toBeTruthy();
            });
          });

          describe('when request states a single day', function () {
            beforeEach(function () {
              compileComponent({
                request: sicknessRequest,
                leaveType: 'sick'
              });
              setTestDates(date2016, date2016To);
              $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
              $rootScope.$digest();
            });

            it('shows single day', function () {
              expect(controller.uiOptions.multipleDays).not.toBeTruthy();
            });
          });

          describe('initializes required documents', function () {
            var testDocumentId = '1';
            var failDocumentId = '3';

            it('checks checkbox', function () {
              expect(controller.isChecked(testDocumentId)).toBeTruthy();
            });

            it('does not check checkbox', function () {
              expect(controller.isChecked(failDocumentId)).toBeFalsy();
            });
          });

          describe('when checking if can submit', function () {
            describe('when sickness reason is not chosen', function () {
              beforeEach(function () {
                controller.request.sickness_reason = null;
              });

              it('does not allow to submit', function () {
                expect(controller.checkSubmitConditions()).toBeFalsy();
              });
            });

            describe('when sickness reason is chosen', function () {
              beforeEach(function () {
                controller.request.sickness_reason = '2';
              });

              it('does not allow to submit', function () {
                expect(controller.checkSubmitConditions()).toBeTruthy();
              });
            });
          });
        });
      });
    });

    describe('when request type is TOIL', function () {
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
            selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {remainder: 0});
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
    });

    describe('when editing an open request', function () {
      var request, expectedOpeningBalance, absenceTypes;

      beforeEach(function () {
        absenceTypes = absenceTypeData.all().values;
        request = leaveRequestData.all().values[0];
        request.status_id = getStatusValueFromName(sharedSettings.statusNames.approved);

        compileComponent({
          mode: 'edit',
          request: LeaveRequestInstance.init(request),
          role: 'manager',
          selectedAbsenceType: absenceTypes[0]
        });

        expectedOpeningBalance = absenceTypes[0].remainder - request.balance_change;
      });

      it('has original opening balance', function () {
        expect(controller.balance.opening).toBe(expectedOpeningBalance);
      });

      describe('when changing leave type', function () {
        beforeEach(function () {
          controller.request.type_id = absenceTypes[1].id;
          expectedOpeningBalance = absenceTypes[1].remainder;

          $rootScope.$broadcast('LeaveRequestPopup::absenceTypeChanged');
          $rootScope.$digest();
        });

        it('uses the opening balance for that leave type', function () {
          expect(controller.balance.opening).toBe(absenceTypes[1].remainder);
        });

        describe('when reverting back to the original leave type', function () {
          it('has original opening balance', function () {
            expect(controller.balance.opening).toBe(expectedOpeningBalance);
          });
        });
      });

      describe('when status is admin approved', function () {
        beforeEach(function () {
          request = leaveRequestData.all().values[0];
          request.status_id = getStatusValueFromName(sharedSettings.statusNames.adminApproved);
          expectedOpeningBalance = absenceTypes[0].remainder - request.balance_change;

          compileComponent({
            mode: 'edit',
            request: LeaveRequestInstance.init(request),
            role: 'manager',
            selectedAbsenceType: absenceTypes[0]
          });
        });

        it('has original opening balance', function () {
          expect(controller.balance.opening).toBe(expectedOpeningBalance);
        });
      });

      describe('when status is "Awaiting Approval"', function () {
        beforeEach(function () {
          request = leaveRequestData.all().values[0];
          request.status_id = getStatusValueFromName(sharedSettings.statusNames.awaitingApproval);
          expectedOpeningBalance = absenceTypes[0].remainder;

          compileComponent({
            mode: 'edit',
            request: LeaveRequestInstance.init(request),
            role: 'manager',
            selectedAbsenceType: absenceTypes[0]
          });
        });

        it('has absence type remainder as opening balance', function () {
          expect(controller.balance.opening).toBe(expectedOpeningBalance);
        });
      });
    });

    describe('time and date inputs watchers', function () {
      beforeEach(function () {
        var absenceTypes = absenceTypeData.all().values;

        compileComponent({
          mode: 'create',
          role: 'admin',
          selectedAbsenceType: absenceTypes[0]
        });
        spyOn(controller, 'performBalanceChangeCalculation').and.callThrough();
      });

      describe('when the calculation unit is "hours"', function () {
        beforeEach(function () {
          selectedAbsenceType.calculation_unit_name = 'hours';

          spyOn(controller, 'dateChangeHandler').and.callThrough();
        });

        describe('when from/to deductions values are set but not changed', function () {
          beforeEach(function () {
            // the amounts are 0, setting them to 0 again still fires the watcher
            controller.uiOptions.times.from.amount = 0;
            controller.uiOptions.times.to.amount = 0;

            $rootScope.$digest();
          });

          it('does not call the balance change calculation function', function () {
            expect(controller.performBalanceChangeCalculation).not.toHaveBeenCalled();
          });

          describe('when from/to deductions values are changed', function () {
            beforeEach(function () {
              controller.uiOptions.times.from.amount = 80;
              controller.uiOptions.times.to.amount = 100;

              $rootScope.$digest();
            });

            it('calls the balance change calculation function', function () {
              expect(controller.performBalanceChangeCalculation).toHaveBeenCalled();
            });
          });
        });

        describe('when "from" time value is changed in UI', function () {
          var fromTime = '19:00';

          beforeEach(function () {
            setTestDates(date2016);

            controller.uiOptions.times.from.time = fromTime;

            $rootScope.$digest();
          });

          it('updates the time in the request instance', function () {
            expect(moment(controller.request.from_date).format('HH:mm')).toBe(fromTime);
          });
        });

        describe('when "to" time value is changed in UI', function () {
          var toTime = '12:45';

          beforeEach(function () {
            setTestDates(undefined, date2017);

            controller.uiOptions.times.to.time = toTime;

            $rootScope.$digest();
          });

          it('updates the time in the request instance', function () {
            expect(moment(controller.request.to_date).format('HH:mm')).toBe(toTime);
          });
        });
      });

      describe('when the calculation unit is "days"', function () {
        beforeEach(function () {
          selectedAbsenceType.calculation_unit_name = 'days';
        });

        describe('when from/to deductions are set', function () {
          beforeEach(function () {
            controller.uiOptions.times.from.amount = 50;
            controller.uiOptions.times.to.amount = 100;

            $rootScope.$digest();
          });

          it('does not call the balance change calculation function', function () {
            expect(LeaveRequestAPI.calculateBalanceChange).not.toHaveBeenCalled();
          });
        });
      });

      describe('when dates are changed', function () {
        beforeEach(function () {
          spyOn(controller.request, 'getWorkDayForDate').and.returnValue($q.reject());
        });

        describe('when the calculation unit is "hours"', function () {
          beforeEach(function () {
            selectedAbsenceType.calculation_unit_name = 'hours';
          });

          describe('when "from" date is changed', function () {
            beforeEach(function () {
              setTestDates(date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).toHaveBeenCalled();
            });
          });

          describe('when "to" date is changed', function () {
            beforeEach(function () {
              setTestDates(null, date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).toHaveBeenCalled();
            });
          });
        });

        describe('when the calculation unit is "days"', function () {
          beforeEach(function () {
            selectedAbsenceType.calculation_unit_name = 'days';
          });

          describe('when "from" date is changed', function () {
            beforeEach(function () {
              setTestDates(date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).not.toHaveBeenCalled();
            });
          });

          describe('when "to" date is changed', function () {
            beforeEach(function () {
              setTestDates(null, date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).not.toHaveBeenCalled();
            });
          });
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

    /**
     * Sets reason on request
     **/
    function setReason () {
      var reason = optionGroupMock.specificObject('hrleaveandabsences_sickness_reason', 'name', 'appointment');
      controller.request.sickness_reason = reason.value;
    }
  });
});
