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
  'leave-absences/mocks/helpers/request-modal-helper',
  'common/mocks/services/hr-settings-mock',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/leave-request-api-mock',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/mocks/apis/public-holiday-api-mock',
  'leave-absences/mocks/apis/work-pattern-api-mock',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, absencePeriodData, absenceTypeData, leaveRequestData, optionGroupMock, helper, requestModalHelper) {
  'use strict';

  describe('leaveRequestPopupDetailsTab', function () {
    var $componentController, $controllerProvider, $provide, $q, $log, $rootScope, $scope, controller,
      sharedSettings, LeaveRequestAPI, AbsenceType, AbsenceTypeAPI, LeaveRequest, LeaveRequestInstance,
      OptionGroup, OptionGroupAPIMock, selectedAbsenceType, WorkPatternAPI, EntitlementAPI;

    var date2013 = '02/02/2013';
    var date2016 = '01/12/2016';
    var date2016InServerFormat = moment(helper.getUTCDate(date2016)).format('YYYY-MM-D'); // Must match the date of `date2016`
    var date2016To = '02/12/2016'; // Must be greater than `date2016`
    var date2017 = '01/02/2017';
    var date2017To = '02/02/2017'; // Must be greater than `date2017`
    var date2017ToInServerFormat = moment(date2017To, 'D/MM/YYYY').format('YYYY-MM-D'); // Must match the date of `date2017To`

    beforeEach(module('common.mocks', 'leave-absences.templates', 'leave-absences.mocks', 'manager-leave',
      function (_$controllerProvider_, _$provide_) {
        $controllerProvider = _$controllerProvider_;
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveRequestAPIMock_, _PublicHolidayAPIMock_, _OptionGroupAPIMock_, _WorkPatternAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
      $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
    }));

    beforeEach(inject(['HR_settingsMock', 'shared-settings', function (_HRSettingsMock_, _sharedSettings_) {
      $provide.value('HR_settings', _HRSettingsMock_);
      sharedSettings = _sharedSettings_;
    }]));

    beforeEach(inject(function (
      _$componentController_, _$q_, _$log_, _$rootScope_, _AbsenceType_, _AbsenceTypeAPI_, _LeaveRequest_,
      _LeaveRequestInstance_, _OptionGroup_, _OptionGroupAPIMock_, _LeaveRequestAPI_, _WorkPatternAPI_, _EntitlementAPI_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      LeaveRequest = _LeaveRequest_;
      LeaveRequestInstance = _LeaveRequestInstance_;
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
    }));

    describe('child controller override', function () {
      describe('when the child controller defines an existing property of the parent component', function () {
        var valueSetInParentController = 2;
        var valueSetInChildController = 1;

        beforeEach(inject(function () {
          $controllerProvider.register('RequestModalDetailsLeaveController', function ($controller, $log, $q, detailsController) {
            detailsController.canCalculateChange = jasmine.createSpy();
            detailsController.initChildController = jasmine.createSpy();
            detailsController.initChildController.and.returnValue($q.resolve());

            detailsController.someProperty = valueSetInChildController;
          });

          compileComponent({ someProperty: valueSetInParentController });
        }));

        it('overrides the properties defined in parent component', function () {
          expect(controller.someProperty).toBe(valueSetInChildController);
        });
      });
    });

    describe('when the user creates a leave request', function () {
      describe('on init', function () {
        beforeEach(function () {
          selectedAbsenceType = _.assign(absenceTypeData.all().values[0], {remainder: 0});

          compileComponent();

          $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
          $rootScope.$digest();
        });

        it('is initialized', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('emits an "add tab" event', function () {
          expect($scope.$emit).toHaveBeenCalledWith('LeaveRequestPopup::addTab', controller);
        });

        it('has leave type as "leave"', function () {
          expect(controller.isLeaveType('leave')).toBeTruthy();
        });

        it('has time interval set to 15 minutes', function () {
          expect(controller.uiOptions.time_interval).toBe(15);
        });

        it('is defined as a required tab', function () {
          expect(controller.isRequired).toBe(true);
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
          it('flushes time deductions immediately', function () {
            expect(controller.uiOptions.times.from.amount).toEqual(0);
            expect(controller.uiOptions.times.to.amount).toEqual(0);
          });
        });

        describe('after from date is selected and it does not belong to any absence period', function () {
          beforeEach(function () {
            spyOn($rootScope, '$broadcast');
            controller.uiOptions.fromDate = helper.getUTCDate('01/01/1800');
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
            controller.period = {};

            togglePublicHolidayRequestForCurrentDate(false);
            requestModalHelper.setTestDates(controller, date2016);

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

          it('updates calendar', function () {
            expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
          });

          it('sets the datepicker boundaries', function () {
            expect(controller.uiOptions.date.to.options.maxDate).toEqual(moment(controller.period.end_date).toDate());
            expect(controller.uiOptions.date.to.options.minDate).toEqual(moment(controller.uiOptions.fromDate).add(1, 'day').toDate());
            expect(controller.uiOptions.date.to.options.initDate).toEqual(controller.uiOptions.date.to.options.minDate);
          });

          describe('after another "from" date is selected from the same absence period', function () {
            beforeEach(function () {
              WorkPatternAPI.getCalendar.calls.reset();
              requestModalHelper.setTestDates(controller, date2016To);
            });

            it('does not update calendar', function () {
              expect(WorkPatternAPI.getCalendar).not.toHaveBeenCalled();
            });
          });

          describe('and from date is weekend', function () {
            var testDate;

            beforeEach(function () {
              testDate = helper.getDate('weekend');
              requestModalHelper.setTestDates(controller, testDate.date);
            });

            it('sets weekend day type', function () {
              expect(controller.requestFromDayTypes[0].label).toEqual('Weekend');
            });
          });

          describe('and from date is non working day', function () {
            var testDate;

            beforeEach(function () {
              testDate = helper.getDate('non_working_day');
              requestModalHelper.setTestDates(controller, testDate.date);
            });

            it('sets non_working_day day type', function () {
              expect(controller.requestFromDayTypes[0].label).toEqual('Non Working Day');
            });
          });

          describe('and from date is working day', function () {
            var testDate;

            beforeEach(function () {
              testDate = helper.getDate('working_day');
              requestModalHelper.setTestDates(controller, testDate.date);
            });

            it('sets non_working_day day type', function () {
              expect(controller.requestFromDayTypes.length).toEqual(3);
            });
          });

          describe('and from date is a public holiday', function () {
            beforeEach(function () {
              togglePublicHolidayRequestForCurrentDate(true);
              requestModalHelper.setTestDates(controller, date2016);
            });

            it('sets public_holiday day type', function () {
              expect(controller.requestFromDayTypes[0].label).toEqual('Public Holiday');
            });
          });
        });

        describe('after to date is selected', function () {
          var toDate;

          beforeEach(function () {
            togglePublicHolidayRequestForCurrentDate(false);
            requestModalHelper.setTestDates(controller, date2016, date2016To);

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
            requestModalHelper.setTestDates(controller, date2016, date2016To);
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

            it('does not calculate balance change', function () {
              // It doesn't calculate balance change since the "To" date will be flushed
              expect(LeaveRequestAPI.calculateBalanceChange).not.toHaveBeenCalled();
            });

            describe('after from date is selected', function () {
              beforeEach(function () {
                requestModalHelper.setTestDates(controller, date2016, date2016To);
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
              requestModalHelper.setTestDates(controller, date2016);
            });

            it('sets from date', function () {
              expect(moment(controller.request.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            });
          });

          describe('when to date is selected', function () {
            beforeEach(function () {
              requestModalHelper.setTestDates(controller, date2016, date2016To);
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

              togglePublicHolidayRequestForCurrentDate(false);
              requestModalHelper.setTestDates(controller, null, date2016);
            });

            it('selects to date type', function () {
              expect(controller.request.to_date_type).toEqual(expectedDayType);
            });
          });

          describe('when from and to are selected', function () {
            beforeEach(function () {
              requestModalHelper.setTestDates(controller, date2016, date2016To);
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
                requestModalHelper.setTestDates(controller, date2016, date2016To);

                controller.request.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');

                controller.dateTypeChangeHandler();
                $rootScope.$digest();
              });

              it('updates change amount', function () {
                expect(controller.balance.change.amount).toEqual(-2);
                expect(controller.request.balance_change).toEqual(controller.balance.change.amount);
              });

              it('updates closing balance', function () {
                expect(controller.balance.closing).toEqual(jasmine.any(Number));
              });
            });
          });

          describe('when balance change is expanded during pagination', function () {
            beforeEach(function () {
              requestModalHelper.setTestDates(controller, date2016, date2016To);
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
            });

            afterEach(function () {
              selectedAbsenceType.calculation_unit_name = 'days';
            });

            it('has a storage for time selectors', function () {
              ['from', 'to'].forEach(function (type) {
                expect(controller.uiOptions.times[type].time).toBeDefined();
                expect(controller.uiOptions.times[type].amount).toBeDefined();
                expect(controller.uiOptions.times[type].maxAmount).toBeDefined();
                expect(controller.uiOptions.times[type].amountExpanded).toBe(false);
              });
            });

            it('defaults data selection to a single day', function () {
              expect(controller.uiOptions.multipleDays).toBeFalsy();
            });

            describe('after changed to multiple days mode', function () {
              beforeEach(function () {
                controller.uiOptions.multipleDays = true;

                controller.daysSelectionModeChangeHandler();
                $rootScope.$digest();
              });

              it('shows both "from" and "to" times', function () {
                expect(controller.uiOptions.times.from.loading).toBe(false);
                expect(controller.uiOptions.times.to.loading).toBe(false);
              });
            });

            describe('after from date is selected', function () {
              var timeFromObject, request, workDayMock;

              beforeEach(function () {
                timeFromObject = controller.uiOptions.times.from;
                request = controller.request;
                workDayMock = leaveRequestData.workDayForDate().values;

                requestModalHelper.setTestDates(controller, date2016);
                $rootScope.$digest();
              });

              it('turns loading indicator off', function () {
                expect(timeFromObject.loading).toBeFalsy();
              });

              it('sets minimum timepicker option', function () {
                expect(timeFromObject.min).toBe(workDayMock.time_from);
              });

              it('sets maximum timepicker option', function () {
                expect(timeFromObject.max).toBe(
                  // 15 minutes earlier than the working pattern end time
                  getMomentDateWithGivenTime(workDayMock.time_to)
                    .subtract(15, 'minutes')
                    .format('HH:mm')
                );
              });

              it('pre-sets default timepicker option same as *minimum*', function () {
                expect(timeFromObject.time).toBe(timeFromObject.min);
              });

              it('sets the default deduction amount same as maximum', function () {
                expect(timeFromObject.amount).toBe(timeFromObject.maxAmount);
              });

              it('sets the "from" date to request in a date+time format', function () {
                expect(request.from_date.length).toBe('YYYY-MM-DD hh:mm'.length);
              });

              describe('and it is a single day request', function () {
                var timeToObject;

                beforeEach(function () {
                  timeToObject = controller.uiOptions.times.to;
                  controller.uiOptions.multipleDays = false;

                  requestModalHelper.setTestDates(controller, date2016);
                });

                it('allows to select end time ("to" time)', function () {
                  expect(timeToObject.loading).toBeFalsy();
                  expect(timeToObject.min).toBe(
                    // 15 minutes later than the working pattern start time
                    getMomentDateWithGivenTime(workDayMock.time_from)
                      .add(15, 'minutes')
                      .format('HH:mm')
                  );
                  expect(timeToObject.max).toBe(workDayMock.time_to);
                  expect(timeToObject.time).toBe(timeToObject.max);
                  expect(timeToObject.disabled).toBeFalsy();
                });

                describe('after to date is selected', function () {
                  beforeEach(function () {
                    requestModalHelper.setTestDates(controller, undefined, date2016To);
                  });

                  describe('after both start and end times are selected', function () {
                    beforeEach(function () {
                      controller.uiOptions.times.from.time =
                        getMomentDateWithGivenTime(workDayMock.time_from)
                          .add(controller.uiOptions.time_interval * 2, 'minutes')
                          .format('HH:mm');
                      controller.uiOptions.times.to.time =
                        getMomentDateWithGivenTime(workDayMock.time_to)
                          .subtract(controller.uiOptions.time_interval * 2, 'minutes')
                          .format('HH:mm');

                      $rootScope.$digest();
                    });

                    it('sets the maximum deduction amount according to the chosen timeframe', function () {
                      expect(controller.uiOptions.times.from.maxAmount).toBe(
                        getTimeDifferenceInHours(controller.uiOptions.times.from.time, controller.uiOptions.times.to.time)
                      );
                    });
                  });
                });
              });

              describe('after mode change to multiple days and to date is selected', function () {
                var timeToObject;

                beforeEach(function () {
                  controller.uiOptions.multipleDays = true;
                  timeToObject = controller.uiOptions.times.to;

                  requestModalHelper.setTestDates(controller, date2016, date2017);
                });

                it('reverts maximum time range for "from" time', function () {
                  expect(timeFromObject.max).toBe(workDayMock.time_to);
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

                it('allows user to select "to" time', function () {
                  expect(timeToObject.disabled).toBeFalsy();
                });

                it('sets the "from" date to request in a date+time format', function () {
                  expect(request.to_date.length).toBe('YYYY-MM-DD hh:mm'.length);
                });

                it('shows the balance', function () {
                  expect(controller.uiOptions.showBalance).toBeTruthy();
                });

                describe('after both start and end times are selected', function () {
                  beforeEach(function () {
                    controller.uiOptions.times.from.time =
                      getMomentDateWithGivenTime(workDayMock.time_from)
                        .add(15, 'minutes')
                        .format('HH:mm');
                    controller.uiOptions.times.to.time =
                      getMomentDateWithGivenTime(workDayMock.time_to)
                        .subtract(30, 'minutes')
                        .format('HH:mm');

                    $rootScope.$digest();
                  });

                  it('sets the maximum "from" deduction amount according to maximum and chosen "from" times', function () {
                    expect(controller.uiOptions.times.from.maxAmount).toBe(
                      getTimeDifferenceInHours(controller.uiOptions.times.from.time, controller.uiOptions.times.from.max)
                    );
                  });

                  it('sets the maximum "to" deduction amount according to minimum and chosen "to" times', function () {
                    expect(controller.uiOptions.times.to.maxAmount).toBe(
                      getTimeDifferenceInHours(controller.uiOptions.times.to.min, controller.uiOptions.times.to.time)
                    );
                  });

                  it('sets the default deductions amounts same as maximum', function () {
                    expect(controller.uiOptions.times.from.amount).toBe(controller.uiOptions.times.from.maxAmount);
                    expect(controller.uiOptions.times.to.amount).toBe(controller.uiOptions.times.to.maxAmount);
                  });
                });

                describe('and it is a single day request', function () {
                  beforeEach(function () {
                    controller.uiOptions.multipleDays = false;

                    requestModalHelper.setTestDates(controller, date2016);
                  });

                  describe('when "time from" and "time to" are set', function () {
                    var timeTo;

                    beforeEach(function () {
                      timeTo = controller.uiOptions.times.to.max;
                      controller.uiOptions.times.from.time = controller.uiOptions.times.from.min;
                      controller.uiOptions.times.to.time = timeTo;

                      $rootScope.$digest();
                    });

                    it('updates the "time to" value in the request "to date"', function () {
                      expect(controller.request.to_date.split(' ')[1]).toBe(timeTo);
                    });
                  });
                });

                describe('if work day info cannot be retrieved', function () {
                  beforeEach(function () {
                    spyOn($rootScope, '$broadcast');
                    spyOn(request, 'getWorkDayForDate').and.returnValue($q.reject());
                    requestModalHelper.setTestDates(controller, date2016, date2017);
                  });

                  it('flushes and disables time and deduction fields', function () {
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

                    requestModalHelper.setTestDates(controller, null, '10/12/2016');
                    requestModalHelper.setTestDates(controller, '11/12/2016');
                  });

                  it('resets "to" times and durations', function () {
                    expect(timesTo.time).toBe('');
                    expect(timesTo.min).toBe('00:00');
                    expect(timesTo.max).toBe('00:00');
                    expect(timesTo.amount).toBe('0');
                    expect(timesTo.maxAmount).toBe('0');
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
            var toDeduction = '1.25';

            beforeEach(function () {
              var status = optionGroupMock.specificValue(
                'hrleaveandabsences_leave_request_status', 'value', '3');

              leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));
              selectedAbsenceType.calculation_unit_name = 'hours';
              leaveRequest.from_date = leaveRequest.from_date.slice(0, 11) + '12:00';
              leaveRequest.to_date = leaveRequest.to_date.slice(0, 11) + '12:15';
              leaveRequest.from_date_amount = fromDeduction;
              leaveRequest.to_date_amount = toDeduction;

              compileComponent({
                mode: 'edit',
                request: leaveRequest,
                role: 'staff'
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
              expect(controller.uiOptions.times.from.amount).toBe(fromDeduction);
              expect(controller.uiOptions.times.to.amount).toBe(toDeduction);
            });

            it('does not recalculate the balance', function () {
              expect(LeaveRequestAPI.calculateBalanceChange).not.toHaveBeenCalled();
            });

            it('will not recalculate the balance on save', function () {
              expect(controller.request.change_balance).not.toBeDefined();
            });

            describe('and is a single day request', function () {
              var workDayMock;
              var fromDeduction = '1';

              beforeEach(function () {
                var status = optionGroupMock.specificValue(
                  'hrleaveandabsences_leave_request_status', 'value', '3');

                workDayMock = leaveRequestData.workDayForDate().values;
                leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));
                selectedAbsenceType.calculation_unit_name = 'hours';
                leaveRequest.from_date = date2016InServerFormat + ' ' +
                  getMomentDateWithGivenTime(workDayMock.time_from)
                    .add(30, 'minutes')
                    .format('HH:mm');
                leaveRequest.to_date = date2016InServerFormat + ' ' + workDayMock.time_to;
                leaveRequest.from_date_amount = fromDeduction;

                compileComponent({
                  mode: 'edit',
                  request: leaveRequest
                });
                $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
                $rootScope.$digest();
              });

              it('sets maximum "from" time boundary', function () {
                expect(controller.uiOptions.times.from.max).toBe(
                  getMomentDateWithGivenTime(workDayMock.time_to)
                    .subtract(15, 'minutes')
                    .format('HH:mm')
                );
              });

              it('sets minimum "to" time boundary', function () {
                expect(controller.uiOptions.times.to.min).toBe(
                  getMomentDateWithGivenTime(workDayMock.time_from)
                    .add(45, 'minutes')
                    .format('HH:mm')
                );
              });

              it('sets time and deduction', function () {
                expect(controller.uiOptions.times.from.time).toBe(moment(leaveRequest.from_date).format('HH:mm'));
                expect(controller.uiOptions.times.to.time).toBe(moment(leaveRequest.to_date).format('HH:mm'));
                expect(controller.uiOptions.times.from.amount).toBe(fromDeduction);
              });
            });

            describe('when received the balance change recalculation event', function () {
              beforeEach(function () {
                $rootScope.$emit('LeaveRequestPopup::recalculateBalanceChange');
                $rootScope.$apply();
              });

              it('recalculates the balance change', function () {
                expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
              });
            });

            describe('if balance change needs to be recalculated on initiation', function () {
              beforeEach(function () {
                compileComponent({
                  mode: 'edit',
                  request: leaveRequest,
                  forceRecalculateBalanceChange: true
                });
                $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
                $rootScope.$digest();
              });

              it('recalculates the balance', function () {
                expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
              });
            });

            describe('when set times are outside the allowed range', function () {
              setRequestAndWorkPatternTimesAndInitComponent({
                requestFromTime: '02:00',
                requestToTime: '23:00',
                workPatternStartTime: '09:00',
                workPatternEndTime: '17:00'
              });

              it('sets from time to minimum allowed time', function () {
                expect(controller.uiOptions.times.from.time).toBe('09:00');
                expect(moment(leaveRequest.from_date).format('HH:mm')).toBe('09:00');
              });

              it('sets to time to maximum allowed time', function () {
                expect(controller.uiOptions.times.to.time).toBe('17:00');
                expect(moment(leaveRequest.to_date).format('HH:mm')).toBe('17:00');
              });
            });

            describe('when work pattern day has change to a non-working day', function () {
              setRequestAndWorkPatternTimesAndInitComponent({
                requestFromTime: '02:00',
                requestToTime: '23:00',
                workPatternStartTime: '',
                workPatternEndTime: ''
              });

              it('sets from time to minimum allowed time', function () {
                expect(controller.uiOptions.times.from.time).toBe('00:00');
                expect(moment(leaveRequest.from_date).format('HH:mm')).toBe('00:00');
              });

              it('sets to time to maximum allowed time', function () {
                expect(controller.uiOptions.times.to.time).toBe('00:00');
                expect(moment(leaveRequest.to_date).format('HH:mm')).toBe('00:00');
              });
            });

            /**
             * Initialises set time for a given work pattern range
             *
             * @param {Object} params
             * @param {String} params.requestFromTime HH:mm
             * @param {String} params.requestToTime HH:mm
             * @param {String} params.workPatternStartTime HH:mm or empty string
             * @param {String} params.workPatternEndTime HH:mm or empty string
             */
            function setRequestAndWorkPatternTimesAndInitComponent (params) {
              beforeEach(function () {
                var status = optionGroupMock.specificValue(
                  'hrleaveandabsences_leave_request_status', 'value', '3');

                leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));
                selectedAbsenceType.calculation_unit_name = 'hours';
                leaveRequest.from_date = moment(leaveRequest.from_date).format('YYYY-MM-DD') + ' ' + params.requestFromTime;
                leaveRequest.to_date = moment(leaveRequest.to_date).format('YYYY-MM-DD') + ' ' + params.requestToTime;
                spyOn(leaveRequest, 'getWorkDayForDate').and.callFake(function () {
                  return $q.resolve({
                    time_from: params.workPatternStartTime,
                    time_to: params.workPatternEndTime,
                    number_of_hours: 4
                  });
                });
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
            }
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
              WorkPatternAPI.getCalendar.calls.reset();
              requestModalHelper.setTestDates(controller, date2017);
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
                requestModalHelper.setTestDates(controller, date2016);
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
                requestModalHelper.setTestDates(controller, date2013);
              });

              it('shows error', function () {
                expect(controller.errors).toEqual(jasmine.any(Array));
              });
            });

            describe('and to date is selected', function () {
              beforeEach(function () {
                requestModalHelper.setTestDates(controller, date2016, date2016To);
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
                requestModalHelper.setTestDates(controller, date2016);
                minDate = moment(helper.getUTCDate(date2016)).add(1, 'd').toDate();
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

                    requestModalHelper.setTestDates(controller, null, to);
                    requestModalHelper.setTestDates(controller, from);
                  });

                  it('does not reset to date to equal from date', function () {
                    expect(controller.request.to_date).not.toEqual(controller.request.from_date);
                  });
                });

                describe('and from date is greater than to date', function () {
                  beforeEach(function () {
                    from = '11/12/2016';
                    to = '10/12/2016';

                    requestModalHelper.setTestDates(controller, null, to);
                    requestModalHelper.setTestDates(controller, from);
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

                requestModalHelper.setTestDates(controller, date2017, date2017To);
                requestModalHelper.setTestDates(controller, date2016);
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
    });

    describe('when the user edits a leave request', function () {
      describe('as a staff', function () {
        var leaveRequestAttributes;

        beforeEach(function () {
          var leaveRequest;
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');

          leaveRequestAttributes = leaveRequestData.findBy('status_id', status);
          leaveRequest = LeaveRequestInstance.init(_.cloneDeep(leaveRequestAttributes));
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

          var params = compileComponent({
            mode: 'edit',
            request: leaveRequest,
            role: 'staff'
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
            expect(controller.request.contact_id).toEqual(CRM.vars.leaveAndAbsences.contactId.toString());
            expect(controller.request.type_id).toEqual(leaveRequestAttributes.type_id);
            expect(controller.request.status_id).toEqual(waitingApprovalStatus.value);
            expect(controller.request.from_date).toEqual(moment(leaveRequestAttributes.from_date).format('YYYY-MM-DD'));
            expect(controller.request.from_date_type).toEqual(leaveRequestAttributes.from_date_type);
            expect(controller.request.to_date).toEqual(moment(leaveRequestAttributes.to_date).format('YYYY-MM-DD'));
            expect(controller.request.to_date_type).toEqual(leaveRequestAttributes.to_date_type);
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

        describe('when the user selects the same from and to date', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(leaveRequestData.findBy('status_id', status));

            leaveRequest.from_date = leaveRequest.to_date = date2017ToInServerFormat;
            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

            compileComponent({
              mode: 'edit',
              request: leaveRequest,
              selectedAbsenceType: selectedAbsenceType
            });

            $rootScope.$broadcast('LeaveRequestPopup::ContactSelectionComplete');
            $rootScope.$digest();
          });

          it('selects single day', function () {
            expect(controller.uiOptions.multipleDays).toBeFalsy();
          });
        });
      });

      describe('as a manager', function () {
        var request, expectedOpeningBalance, absenceTypes;

        beforeEach(function () {
          absenceTypes = absenceTypeData.all().values;
          request = leaveRequestData.all().values[0];
          request.status_id = helper.getStatusValueFromName(sharedSettings.statusNames.approved);

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

        describe('when status is "Admin Approved"', function () {
          beforeEach(function () {
            request = leaveRequestData.all().values[0];
            request.status_id = helper.getStatusValueFromName(sharedSettings.statusNames.adminApproved);
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
            request.status_id = helper.getStatusValueFromName(sharedSettings.statusNames.awaitingApproval);
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
    });

    describe('when the user views a leave request', function () {
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

      it('stores the leave request', function () {
        expect(controller.request).toEqual(leaveRequest);
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
          controller.uiOptions.multipleDays = true;

          spyOn(controller, 'dateChangeHandler').and.callThrough();
        });

        describe('when balance is recalculated on the front end', function () {
          beforeEach(function () {
            spyOn(controller, 'canCalculateChange').and.returnValue(true);
            controller.performBalanceChangeCalculation();
            $rootScope.$digest();
          });

          it('tells the backend to recalculate the balance as well', function () {
            expect(controller.request.change_balance).toBe(true);
          });
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
            requestModalHelper.setTestDates(controller, date2016);

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
            requestModalHelper.setTestDates(controller, undefined, date2017);

            controller.uiOptions.times.to.time = toTime;

            $rootScope.$digest();
          });

          it('updates the time in the request instance', function () {
            expect(moment(controller.request.to_date).format('HH:mm')).toBe(toTime);
          });
        });

        describe('when it is a single day request and date is set', function () {
          beforeEach(function () {
            controller.uiOptions.multipleDays = false;

            requestModalHelper.setTestDates(controller, date2017);
          });

          describe('when start time is set and it is greater than or equal to end time', function () {
            beforeEach(function () {
              controller.uiOptions.times.to.max = '20:00';
              controller.uiOptions.times.to.time = '19:00';

              $rootScope.$digest();

              controller.uiOptions.times.from.time = '19:00';

              $rootScope.$digest();
            });

            it('flushes the end time', function () {
              expect(controller.uiOptions.times.to.time).toBe('');
            });
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
            controller.uiOptions.multipleDays = true;

            $rootScope.$digest();
          });

          describe('when "from" date is changed', function () {
            beforeEach(function () {
              requestModalHelper.setTestDates(controller, date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).toHaveBeenCalledWith(
                date2016InServerFormat);
            });
          });

          describe('when "to" date is changed', function () {
            beforeEach(function () {
              requestModalHelper.setTestDates(controller, null, date2016);
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
              requestModalHelper.setTestDates(controller, date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).not.toHaveBeenCalled();
            });
          });

          describe('when "to" date is changed', function () {
            beforeEach(function () {
              requestModalHelper.setTestDates(controller, null, date2016);
            });

            it('loads the time and deduction ranges', function () {
              expect(controller.request.getWorkDayForDate).not.toHaveBeenCalled();
            });
          });
        });
      });
    });

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
      params.request = params.request || LeaveRequestInstance.init();
      $scope = $rootScope.$new();

      requestModalHelper.addDefaultComponentParams(params);
      spyOn($scope, '$emit').and.callThrough();

      controller = $componentController(
        'leaveRequestPopupDetailsTab',
        { $scope: $scope },
        params
      );

      $rootScope.$digest();

      return params;
    }

    /**
     * Returns a date with a given time
     *
     * @param  {String} time in HH:mm or hh:mm formats
     * @return {Moment}
     */
    function getMomentDateWithGivenTime (time) {
      return moment()
        .set({
          'hours': time.split(':')[0],
          'minutes': time.split(':')[1]
        });
    }

    /**
     * Calculates time difference in hours
     *
     * @param  {String} timeFrom in HH:mm format
     * @param  {String} timeTo in HH:mm format
     * @return {String} amount of hours, eg. '7.5'
     */
    function getTimeDifferenceInHours (timeFrom, timeTo) {
      return moment.duration(timeTo)
        .subtract(moment.duration(timeFrom)).asHours().toString();
    }

    /**
     * Toggles whether there is a "public_holiday" leave requests
     * for the current date (for the purpose of the test it doesn't matter if
     * the current date is the "from" or "to" date)
     *
     * @param {Boolean} addPublicHolidayRequest
     */
    function togglePublicHolidayRequestForCurrentDate (addPublicHolidayRequest) {
      var spy;

      if (typeof LeaveRequest.all.calls !== 'undefined') {
        spy = LeaveRequest.all;
      } else {
        spy = spyOn(LeaveRequest, 'all');
      }

      spy.and.returnValue($q.resolve({
        list: addPublicHolidayRequest ? [jasmine.any(Object)] : []
      }));
    }
  });
});
