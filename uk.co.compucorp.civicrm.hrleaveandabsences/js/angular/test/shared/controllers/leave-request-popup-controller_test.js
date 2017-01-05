(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'common/angular',
    'mocks/data/option-group-mock-data',
    'common/angularMocks',
    'leave-absences/shared/config',
    'common/mocks/services/hr-settings-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/entitlement-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'leave-absences/shared/directives/leave-request-popup',
    'leave-absences/shared/models/absence-period-model',
    'leave-absences/shared/models/absence-type-model',
    'leave-absences/shared/models/entitlement-model',
    'leave-absences/shared/models/calendar-model',
    'leave-absences/shared/models/leave-request-model',
    'leave-absences/shared/models/public-holiday-model',
    'leave-absences/shared/controllers/leave-request-popup-controller',
    'leave-absences/shared/models/instances/leave-request-instance',
  ], function (_, moment, angular, optionGroupMock) {
    'use strict';

    describe('LeaveRequestPopupCtrl', function () {
      var $compile, $log, $rootScope, $ctrl, directive, $uibModal, modalInstanceSpy, $scope, $q,
        $controllerScope, $provide, DateFormat, LeaveRequestInstance, serverDateFormat = 'YYYY-MM-DD';;

      beforeEach(module('leave-absences.templates', 'leave-absences.directives', 'leave-absences.controllers',
        'leave-absences.models', 'leave-absences.mocks', 'common.mocks', 'leave-absences.models.instances',
        function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(function (_AbsencePeriodAPIMock_, _HR_settingsMock_, _AbsenceTypeAPIMock_,
        _EntitlementAPIMock_, _WorkPatternAPI_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPI_);
        $provide.value('HR_settings', _HR_settingsMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      }));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$uibModal_, _LeaveRequestInstance_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $uibModal = _$uibModal_;
        LeaveRequestInstance = _LeaveRequestInstance_;
        spyOn($log, 'debug');
        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);
      }));

      beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
        $scope = _$rootScope_.$new();
        $q = _$q_;

        $ctrl = _$controller_('LeaveRequestPopupCtrl', {
          $scope: $scope,
          $uibModalInstance: modalInstanceSpy,
          baseData: {
            contactId: 202
          }
        });
        $scope.$digest();
      }));

      it('is called', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('dialog is open', function () {

        describe('before date selected', function () {

          beforeEach(function () {
            $scope.$digest();
          });

          it('selects the current period', function () {
            expect($ctrl.period.current).toBeTruthy();
          });

          it('creates an instance of leave request', function () {
            expect($ctrl.leaveRequest).toBeDefined();
          });

          it('with current absence period', function () {
            expect($ctrl.period).toBeDefined();
          });

          it('creates an instance of leave request', function () {
            expect($ctrl.leaveRequest).toBeDefined();
            expect($ctrl.leaveRequest.contact_id).toBeDefined();
            expect($ctrl.leaveRequest.from_date).not.toBeDefined();
            expect($ctrl.leaveRequest.to_date).not.toBeDefined();
          });

          it('with absence types', function () {
            expect($ctrl.absenceTypes).toBeDefined();
            expect($ctrl.absenceTypes.length).toBeGreaterThan(0);
            expect($ctrl.selectedAbsenceType).toEqual($ctrl.absenceTypes[0]);
          });

          it('calendar is empty', function () {
            expect($ctrl.uiOptions.fromDate).not.toBeDefined();
            expect($ctrl.uiOptions.toDate).not.toBeDefined();
          });

          it('day types are empty', function () {
            expect($ctrl.uiOptions.selectedFromType).not.toBeDefined();
            expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
          });

          it('balance section is empty', function () {
            expect($ctrl.uiOptions.showBalance).toBeFalsy();
            expect($ctrl.balance.opening).toEqual(0);
          });

          it('with work pattern', function () {
            expect($ctrl.calendar).toBeDefined();
            expect($ctrl.calendar.days).toBeDefined();
          });
        });

        describe('after from date is selected', function () {

          beforeEach(function () {
            $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, true);
            $scope.$digest();
          });

          it('with balance changes', function () {
            expect($ctrl.balance).toBeDefined();
            expect($ctrl.balance.opening).toBeDefined();
            expect($ctrl.balance.change).toBeDefined();
            expect($ctrl.balance.closing).toBeDefined();
          });

          it('will define from day date and type', function () {
            expect($ctrl.leaveRequest.from_date).toBeDefined();
            expect($ctrl.uiOptions.selectedFromType).toBeDefined();
            expect($ctrl.leaveRequest.from_date_type).toBeDefined();
          });
        });

        describe('after to date is selected', function () {

          beforeEach(function () {
            $ctrl.onDateChange(new Date(), false);
            $scope.$digest();
          });

          it('to day date and type are defined', function () {
            expect($ctrl.leaveRequest.to_date).toBeDefined();
            expect($ctrl.uiOptions.selectedToType).toBeDefined();
            expect($ctrl.leaveRequest.to_date_type).toBeDefined();
          });
        });

        describe('from and to dates are selected', function () {

          beforeEach(function () {
            $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, true);
            $ctrl.onDateChange($ctrl.uiOptions.toDate, false);
            $scope.$digest();
          });

          it('balance is displayed', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });
        });
      });

      describe('when user cancels dialog (clicks X), or back button', function () {

        beforeEach(function () {
          $ctrl.cancel();
        });

        it('closes model', function () {
          expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
        });
      });

      describe('leave absence types', function () {

        describe('view all', function () {

          beforeEach(function () {
            $scope.$digest();
          });

          it('shows all items', function () {
            expect($ctrl.absenceTypes.length).toBeGreaterThan(0);
          });
        });

        describe('change selection', function () {
          var beforeChangeAbsenceType, afterChangeAbsenceType;

          beforeEach(function () {
            beforeChangeAbsenceType = $ctrl.selectedAbsenceType;
            $ctrl.selectedAbsenceType = $ctrl.absenceTypes[1];

            $ctrl.onAbsenceTypeChange();
            afterChangeAbsenceType = $ctrl.selectedAbsenceType;
            $scope.$digest();
          });

          it('selects another absence type', function () {
            expect(beforeChangeAbsenceType.id).not.toEqual(afterChangeAbsenceType.id);
          });
        });
      });

      describe('number of days selection', function () {

        describe('multiple', function () {

          it('selects by default', function () {
            expect($ctrl.uiOptions.multipleDays).toBeTruthy();
          });

        });

        describe('single', function () {

          beforeEach(function () {
            $ctrl.uiOptions.multipleDays = false;
            $ctrl.changeInNoOfDays();
            $scope.$digest();
          });

          it('selects single day', function () {
            expect($ctrl.uiOptions.multipleDays).toBeFalsy();
          });

          it('hides to date and type', function () {
            expect($ctrl.uiOptions.toDate).not.toBeDefined();
            expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
          });

          it('will reset balance and types', function () {
            expect($ctrl.uiOptions.selectedFromType).not.toBeDefined();
            expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
            expect($ctrl.balance.closing).toEqual(0);
            expect($ctrl.balance.change.amount).toEqual(0);
          });
        });
      });

      describe('calendar', function () {

        it('no dates are selected by default', function () {
          expect($ctrl.uiOptions.fromDate).not.toBeDefined();
          expect($ctrl.uiOptions.toDate).not.toBeDefined();
        });

        describe('from date', function () {

          beforeEach(function () {
            $ctrl.onDateChange(new Date(), true);
            $scope.$digest();
          });

          it('can change date', function () {
            expect(moment($ctrl.leaveRequest.from_date, serverDateFormat, true).isValid()).toBe(true);
          });

        });

        describe('to date', function () {

          beforeEach(function () {
            $ctrl.onDateChange(new Date(), false);
            $scope.$digest();
          });

          it('can change date', function () {
            expect(moment($ctrl.leaveRequest.to_date, serverDateFormat, true).isValid()).toBe(true);
          });

        });
      });

      describe('day types', function () {

        it('by default no selection', function () {
          expect($ctrl.uiOptions.selectedFromType).not.toBeDefined();
          expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
        });

        describe('from', function () {

          beforeEach(function () {
            spyOn($ctrl, 'filterLeaveRequestDayTypes').and.callThrough();
            $ctrl.onDateChange(new Date(), true);
            $scope.$digest();
          });

          it('by default selects first day type', function () {
            expect($ctrl.uiOptions.selectedFromType).toEqual($ctrl.leaveRequestFromDayTypes[0]);
          });

          it('will filter based on current date', function () {
            expect($ctrl.filterLeaveRequestDayTypes).toHaveBeenCalledWith(jasmine.any(Date), jasmine.any(Boolean));
          });

          describe('is public holiday', function () {

            beforeEach(function () {
              $ctrl.uiOptions.fromDate = new Date('01/01/2016');
              $ctrl.filterLeaveRequestDayTypes($ctrl.uiOptions.fromDate, true);
              $scope.$digest();
            });

            it('should', function () {
              expect($ctrl.leaveRequestFromDayTypes.length).toEqual(1);
            });
          });

        });

        describe('to', function () {

          beforeEach(function () {
            $ctrl.onDateChange(new Date(), false);
            $scope.$digest();
          });

          it('by default selects first day type', function () {
            expect($ctrl.uiOptions.selectedToType).toEqual($ctrl.leaveRequestToDayTypes[0]);
          });

          describe('change selection', function () {
            var before, after;

            beforeEach(function () {
              before = $ctrl.uiOptions.selectedToType;
              $ctrl.uiOptions.selectedToType = $ctrl.leaveRequestToDayTypes[1];
              $ctrl.calculateBalanceChange();
              after = $ctrl.uiOptions.selectedToType;
              $scope.$digest();
            });

            it('success', function () {
              expect($ctrl.leaveRequest.to_date_type).toEqual(after.name);
            });
          });
        });

        describe('from and to', function () {

          beforeEach(function () {
            spyOn($ctrl, 'calculateBalanceChange').and.callThrough();
            $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, true);
            $ctrl.onDateChange($ctrl.uiOptions.toDate, false);
            $scope.$digest();
          });

          it('will calculate balance change', function () {
            expect($ctrl.calculateBalanceChange).toHaveBeenCalled();
          });
        });
      });

      describe('calculate balance', function () {

        describe('opening', function () {

          it('has default', function () {
            expect($ctrl.balance.opening).toEqual(0);
          });

        });

        describe('change', function () {

          it('has default', function () {
            expect($ctrl.balance.change.amount).toEqual(0);
          });

          describe('when day type changed', function () {

            describe('for single day', function () {

              beforeEach(function () {
                //select half_day_am  to get single day mock data
                //$ctrl.uiOptions.multipleDays = false;
                //$ctrl.uiOptions.fromDate = new Date();
                $ctrl.uiOptions.selectedFromType = optionGroupMock.specificObject('hrleaveandabsences_leave_request_day_type', 'name', 'half_day_am');
                $ctrl.calculateBalanceChange();
                $scope.$digest();
              });

              it('will update balance', function () {
                expect($ctrl.balance.change.amount).toEqual(-0.5);
              });

              it('will update closing balance', function () {
                expect($ctrl.balance.closing).toEqual(-0.5);
              });
            });
            describe('for multiple days', function () {

              beforeEach(function () {
                $ctrl.uiOptions.multipleDays = true;
                //select all_day to get multiple day mock data
                $ctrl.onDateChange(new Date(), true);
                $ctrl.onDateChange(new Date(), false);
                $ctrl.uiOptions.selectedFromType = optionGroupMock.specificObject('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');
                $ctrl.calculateBalanceChange();
                $scope.$digest();
              });

              it('will update change amount', function () {
                expect($ctrl.balance.change.amount).toEqual(-2);
              });

              it('will update closing balance', function () {
                expect($ctrl.balance.closing).toEqual(-2);
              });
            });
          });
        });

        describe('change details', function () {

          describe('hidden', function () {

            it('by default', function () {
              expect($ctrl.uiOptions.isChangeExpanded).toBeFalsy();
            });
          });

          describe('when expanded', function () {

            beforeEach(function () {
              $ctrl.uiOptions.isChangeExpanded = true;
            });

            it('will display balance breakdown', function () {
              expect($ctrl.uiOptions.isChangeExpanded).toBeTruthy();
            });

            describe('during pagination', function () {

              describe('init', function () {

                it('should', function () {
                  expect($ctrl.pagination.totalItems).toEqual(0);
                });
              });

              describe('after date is selected', function () {

                beforeEach(function () {
                  $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
                  $ctrl.onDateChange($ctrl.uiOptions.fromDate, true);
                  $ctrl.onDateChange($ctrl.uiOptions.toDate, false);
                  $scope.$digest();
                });

                it('will select default page', function () {
                  expect($ctrl.pagination.currentPage).toEqual(1);
                });

                it('should set totalItems', function () {
                  expect($ctrl.pagination.totalItems).toBeGreaterThan(0);
                });

                describe('change selected page', function () {
                  var beforeFilteredItems;

                  beforeEach(function () {
                    beforeFilteredItems = $ctrl.pagination.filteredbreakdown;
                    $ctrl.pagination.currentPage = 2;
                    $ctrl.pagination.pageChanged();
                  });

                  it('should change current page', function () {
                    expect($ctrl.pagination.currentPage).not.toEqual(1);
                  });

                  it('should change filtered data', function () {
                    expect($ctrl.pagination.filteredbreakdown[0]).not.toEqual(beforeFilteredItems[0]);
                  });
                });
              });
            });
          });
        });
      });

      describe('save leave request', function () {

        describe('with invalid fields', function () {

          it('does not have required fields', function () {
            expect($ctrl.leaveRequest.from_date).not.toBeDefined();
          });

          describe('on submit', function () {

            beforeEach(function () {
              $ctrl.submit();
              $scope.$digest();
            });

            it('should fail', function () {
              expect($ctrl.error).toEqual(jasmine.any(Object));
            });
          });
        });

        describe('with valid fields', function () {

          beforeEach(function () {
            $ctrl.onDateChange(new Date(), true);
            $ctrl.onDateChange(new Date(), false);
            $scope.$digest();
          });

          it('has all required fields', function () {
            expect($ctrl.leaveRequest.id).not.toBeDefined();
            expect($ctrl.leaveRequest.from_date).toBeDefined();
          });

          describe('on submit', function () {

            beforeEach(function () {
              spyOn($rootScope, '$emit');
              $ctrl.submit();
              $scope.$digest();
            });

            it('is successful', function () {
              expect($ctrl.error).not.toEqual(jasmine.any(Object));
              expect($ctrl.leaveRequest.id).toBeDefined();
            });

            it('sends event', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::new', $ctrl.leaveRequest);
            });

            describe('when balance change is negative', function () {

              beforeEach(function () {
                $ctrl.selectedAbsenceType = $ctrl.absenceTypes[1];
                $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
                $ctrl.onDateChange($ctrl.uiOptions.fromDate, true);
                $ctrl.onDateChange($ctrl.uiOptions.toDate, false);
                $scope.$digest();
                $ctrl.submit();
                $scope.$digest();
              });

              it('will not save', function () {
                expect($ctrl.error).toBeDefined();
              });

              describe('and allow_overuse is set', function () {

                beforeEach(function () {
                  $ctrl.selectedAbsenceType = $ctrl.absenceTypes[2];
                  $ctrl.submit();
                  $scope.$digest();
                });

                it('will save', function () {
                  expect($ctrl.error).not.toBeDefined();
                });
              });
            });
          });
        });
      });
    });
  });
})(CRM);
