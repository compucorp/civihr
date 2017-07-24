/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'mocks/data/option-group-mock-data',
    'mocks/data/leave-request-data',
    'mocks/helpers/helper',
    'common/modules/dialog',
    'leave-absences/shared/config',
    'common/mocks/services/hr-settings-mock',
    'common/mocks/services/file-uploader-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/entitlement-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/option-group-api-mock',
    'mocks/apis/public-holiday-api-mock',
    'common/mocks/services/api/contact-mock',
    'leave-absences/shared/controllers/sub-controllers/leave-request-ctrl',
    'leave-absences/shared/modules/shared-settings'
  ], function (_, moment, optionGroupMock, mockData, helper) {
    'use strict';

    describe('LeaveRequestCtrl', function () {
      var $log, $rootScope, $ctrl, modalInstanceSpy, $scope, $q, dialog, $controller,
        $provide, sharedSettings, AbsenceTypeAPI, AbsencePeriodAPI, LeaveRequestInstance,
        Contact, ContactAPIMock, EntitlementAPI, LeaveRequestAPI, WorkPatternAPI, parentRequestCtrl;
      var date2016 = '01/12/2016';
      var date2017 = '02/02/2017';
      var date2013 = '02/02/2013';
      var dateServer2017 = '2017-02-02';
      var role = 'staff'; // change this value to set other roles

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks', 'common.dialog', 'leave-absences.settings',
        function (_$provide_, $exceptionHandlerProvider) {
          $provide = _$provide_;
          // this will consume all throw
          $exceptionHandlerProvider.mode('log');
        }));

      beforeEach(inject(function (
        _AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_, _EntitlementAPIMock_, _WorkPatternAPIMock_,
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      }));

      beforeEach(inject(['api.contact.mock', 'shared-settings', 'HR_settingsMock', function (_ContactAPIMock_, _sharedSettings_, _HRSettingsMock_) {
        $provide.value('api.contact', _ContactAPIMock_);
        $provide.value('HR_settings', _HRSettingsMock_);
        ContactAPIMock = _ContactAPIMock_;
        sharedSettings = _sharedSettings_;

        $provide.value('checkPermissions', function (permission) {
          var returnValue = false;
          if (role === 'admin') {
            returnValue = permission === sharedSettings.permissions.admin.administer;
          }
          if (role === 'manager') {
            returnValue = permission === sharedSettings.permissions.ssp.manage;
          }

          return $q.resolve(returnValue);
        });
      }]));

      beforeEach(inject(function (_$log_, _$controller_, _$rootScope_, _$q_, _dialog_,
        _AbsenceTypeAPI_, _AbsencePeriodAPI_, _Contact_, _EntitlementAPI_, _Entitlement_,
        _LeaveRequestInstance_, _LeaveRequestAPI_, _WorkPatternAPI_) {
        $log = _$log_;
        $rootScope = _$rootScope_;
        $controller = _$controller_;
        $q = _$q_;
        dialog = _dialog_;

        Contact = _Contact_;
        EntitlementAPI = _EntitlementAPI_;
        LeaveRequestAPI = _LeaveRequestAPI_;
        WorkPatternAPI = _WorkPatternAPI_;
        AbsenceTypeAPI = _AbsenceTypeAPI_;
        AbsencePeriodAPI = _AbsencePeriodAPI_;

        LeaveRequestInstance = _LeaveRequestInstance_;

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });

        spyOn(AbsencePeriodAPI, 'all').and.callThrough();
        spyOn(AbsenceTypeAPI, 'all').and.callThrough();
        spyOn(LeaveRequestAPI, 'calculateBalanceChange').and.callThrough();
        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
        spyOn(EntitlementAPI, 'all').and.callThrough();

        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);
      }));

      describe('staff opens request popup', function () {
        beforeEach(inject(function () {
          var directiveOptions = {
            contactId: CRM.vars.leaveAndAbsences.contactId,
            isSelfRecord: true
          };

          initTestController(directiveOptions);
          parentRequestCtrl = $controller('RequestCtrl');
        }));

        it('is called', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('inherited from request controller', function () {
          expect($ctrl instanceof parentRequestCtrl.constructor).toBe(true);
        });

        it('getStatuses returns an array', function () {
          expect($ctrl.getStatuses()).toEqual(jasmine.any(Array));
        });

        describe('when initialized', function () {
          describe('before date is selected', function () {
            beforeEach(function () {
              $scope.$digest();
            });

            it('has leave type set to leave', function () {
              expect($ctrl.isLeaveType('leave')).toBeTruthy();
            });

            it('has absence period is set', function () {
              expect($ctrl.period).toEqual(jasmine.any(Object));
            });

            it('has current period selected', function () {
              expect($ctrl.period.current).toBeTruthy();
            });

            it('has absence types loaded', function () {
              expect($ctrl.absenceTypes).toBeDefined();
              expect($ctrl.absenceTypes.length).toBeGreaterThan(0);
            });

            it('has first absence type selected', function () {
              expect($ctrl.request.type_id).toEqual($ctrl.absenceTypes[0].id);
            });

            it('has no dates selected', function () {
              expect($ctrl.uiOptions.fromDate).not.toBeDefined();
              expect($ctrl.uiOptions.toDate).not.toBeDefined();
            });

            it('has no day types selected', function () {
              expect($ctrl.uiOptions.selectedFromType).not.toBeDefined();
              expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
            });

            it('does not show balance', function () {
              expect($ctrl.uiOptions.showBalance).toBeFalsy();
              expect($ctrl.balance.opening).toEqual(jasmine.any(Number));
            });

            it('has nil balance change amount', function () {
              expect($ctrl.balance.change.amount).toEqual(0);
            });

            it('has balance change hidden', function () {
              expect($ctrl.uiOptions.isChangeExpanded).toBeFalsy();
            });

            it('has nil total items for balance change pagination', function () {
              expect($ctrl.pagination.totalItems).toEqual(0);
            });

            it('has days of work pattern loaded', function () {
              expect($ctrl.calendar).toBeDefined();
              expect($ctrl.calendar.days).toBeDefined();
            });

            it('gets absence types with false sick param', function () {
              expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({
                is_sick: false
              });
            });

            describe('leave request instance', function () {
              it('has new instance created', function () {
                expect($ctrl.request).toEqual(jasmine.any(Object));
              });

              it('has contact_id set', function () {
                expect($ctrl.request.contact_id).toBeDefined();
              });

              it('does not have from/to dates set', function () {
                expect($ctrl.request.from_date).toBeNull();
                expect($ctrl.request.to_date).toBeNull();
              });
            });

            describe('multiple days', function () {
              it('is selected by default', function () {
                expect($ctrl.uiOptions.multipleDays).toBeTruthy();
              });
            });
          });

          describe('after from date is selected', function () {
            var fromDate;

            beforeEach(function () {
              setTestDates(date2016);
              fromDate = moment($ctrl.uiOptions.fromDate).format(sharedSettings.serverDateFormat);
            });

            it('has balance change defined', function () {
              expect($ctrl.balance).toEqual(jasmine.any(Object));
              expect($ctrl.balance.opening).toEqual(jasmine.any(Number));
              expect($ctrl.balance.change).toEqual(jasmine.any(Object));
              expect($ctrl.balance.closing).toEqual(jasmine.any(Number));
            });

            it('has from date set', function () {
              expect($ctrl.request.from_date).toEqual(fromDate);
            });

            it('selects first day type', function () {
              expect($ctrl.request.from_date_type).toEqual('1');
            });

            describe('and from date is weekend', function () {
              var testDate;

              beforeEach(function () {
                testDate = helper.getDate('weekend');
                setTestDates(testDate.date);
              });

              it('sets weekend day type', function () {
                expect($ctrl.requestFromDayTypes[0].label).toEqual('Weekend');
              });
            });

            describe('and from date is non working day', function () {
              var testDate;

              beforeEach(function () {
                testDate = helper.getDate('non_working_day');
                setTestDates(testDate.date);
              });

              it('sets non_working_day day type', function () {
                expect($ctrl.requestFromDayTypes[0].label).toEqual('Non Working Day');
              });
            });

            describe('and from date is working day', function () {
              var testDate;

              beforeEach(function () {
                testDate = helper.getDate('working_day');
                setTestDates(testDate.date);
              });

              it('sets non_working_day day type', function () {
                expect($ctrl.requestFromDayTypes.length).toEqual(3);
              });
            });
          });

          describe('after to date is selected', function () {
            var toDate;

            beforeEach(function () {
              setTestDates(date2016, date2016);
              toDate = moment($ctrl.uiOptions.toDate).format(sharedSettings.serverDateFormat);
            });

            it('sets to date', function () {
              expect($ctrl.request.to_date).toEqual(toDate);
            });

            it('select first day type', function () {
              expect($ctrl.request.to_date_type).toEqual('1');
            });
          });

          describe('from and to dates are selected', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016);
            });

            it('does show balance change', function () {
              expect($ctrl.uiOptions.showBalance).toBeTruthy();
            });
          });
        });

        describe('formatDateTime()', function () {
          var returnValue;

          beforeEach(function () {
            returnValue = $ctrl.formatDateTime('2017-06-14 12:15:18');
          });

          it('returns date time in user format', function () {
            expect(returnValue).toBe('14/06/2017 12:15');
          });
        });

        describe('when user cancels dialog (clicks X), or back button', function () {
          beforeEach(function () {
            $ctrl.dismissModal();
          });

          it('closes model', function () {
            expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
          });
        });

        describe('leave absence types', function () {
          describe('on change selection', function () {
            var beforeChangeAbsenceType, afterChangeAbsenceType;

            beforeEach(function () {
              beforeChangeAbsenceType = $ctrl.absenceTypes[0];
              $ctrl.request.type_id = $ctrl.absenceTypes[1].id;
              $ctrl.updateBalance();
              afterChangeAbsenceType = $ctrl.absenceTypes[1];
              $scope.$digest();
            });

            it('selects another absence type', function () {
              expect(beforeChangeAbsenceType.id).not.toEqual(afterChangeAbsenceType.id);
            });

            it('updates balance', function () {
              expect($ctrl.balance.opening).toEqual(afterChangeAbsenceType.remainder);
            });
          });
        });

        describe('number of days selection without date selection', function () {
          describe('when switching to single day', function () {
            beforeEach(function () {
              $ctrl.uiOptions.multipleDays = false;
              $ctrl.changeInNoOfDays();
              $scope.$digest();
            });

            it('hides to date and type', function () {
              expect($ctrl.uiOptions.toDate).not.toBeDefined();
              expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
            });

            it('resets balance and types', function () {
              // we expect balance change to be 0 because "from" and "to" dates are equal in a single day mode
              expect($ctrl.balance.change.amount).toEqual(0);
              // if balance change amount is 0 we expect closing balance be equal to opening balance
              expect($ctrl.balance.closing).toEqual($ctrl.balance.opening);
            });

            it('shows no balance', function () {
              expect($ctrl.uiOptions.showBalance).toBeFalsy();
            });

            describe('after from date is selected', function () {
              beforeEach(function () {
                setTestDates(date2016);
              });

              it('sets from and to dates', function () {
                expect($ctrl.request.from_date).not.toBeNull();
                expect($ctrl.request.to_date).not.toBeNull();
              });

              it('shows balance', function () {
                expect($ctrl.uiOptions.showBalance).toBeTruthy();
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
              expect(moment($ctrl.request.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
            });
          });

          describe('when to date is selected', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016);
            });

            it('sets to date', function () {
              expect(moment($ctrl.request.to_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
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
              expect($ctrl.request.to_date_type).toEqual(expectedDayType);
            });
          });

          describe('when from and to are selected', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016);
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
                // select half_day_am  to get single day mock data
                $ctrl.request.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'half_day_am');
                $ctrl.calculateBalanceChange();
                $scope.$digest();
              });

              it('updates balance', function () {
                expect($ctrl.balance.change.amount).toEqual(jasmine.any(Number));
              });

              it('updates closing balance', function () {
                expect($ctrl.balance.closing).toEqual(jasmine.any(Number));
              });
            });

            describe('for multiple days', function () {
              beforeEach(function () {
                $ctrl.uiOptions.multipleDays = true;
                // select all_day to get multiple day mock data
                setTestDates(date2016, date2016);
                $ctrl.request.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');
                $ctrl.calculateBalanceChange();
                $scope.$digest();
              });

              it('updates change amount', function () {
                expect($ctrl.balance.change.amount).toEqual(-2);
              });

              it('updates closing balance', function () {
                expect($ctrl.balance.closing).toEqual(jasmine.any(Number));
              });
            });
          });

          describe('when balance change is expanded during pagination', function () {
            beforeEach(function () {
              setTestDates(date2016, date2016);
            });

            it('selects default page', function () {
              expect($ctrl.pagination.currentPage).toEqual(1);
            });

            it('sets totalItems', function () {
              expect($ctrl.pagination.totalItems).toBeGreaterThan(0);
            });

            describe('when page selection changes', function () {
              var beforeFilteredItems;

              beforeEach(function () {
                beforeFilteredItems = $ctrl.pagination.filteredbreakdown;
                $ctrl.pagination.currentPage = 2;
                $ctrl.pagination.pageChanged();
              });

              it('changes current page', function () {
                expect($ctrl.pagination.currentPage).not.toEqual(1);
              });

              it('changes filtered data', function () {
                expect($ctrl.pagination.filteredbreakdown[0]).not.toEqual(beforeFilteredItems[0]);
              });
            });
          });
        });

        describe('save leave request', function () {
          describe('does not allow multiple save', function () {
            beforeEach(function () {
              $ctrl.submit();
            });

            it('user cannot submit again', function () {
              expect($ctrl.submitting).toBeTruthy();
            });

            it('submit does not create request again', function () {
              spyOn($ctrl.request, 'create').and.callThrough();
              $ctrl.submit();
              expect($ctrl.request.create).not.toHaveBeenCalled();
            });
          });

          describe('when submit with invalid fields', function () {
            beforeEach(function () {
              $ctrl.submit();
              $scope.$digest();
            });

            it('fails with error', function () {
              expect($ctrl.errors).toEqual(jasmine.any(Array));
            });

            it('does not allow user to submit', function () {
              expect($ctrl.canSubmit()).toBeFalsy();
            });
          });

          describe('when submit with valid fields', function () {
            beforeEach(function () {
              spyOn($rootScope, '$emit');
              setTestDates(date2016, date2016);
              // entitlements are randomly generated so resetting them to positive here
              $ctrl.balance.closing = 1;

              $ctrl.submit();
              $scope.$digest();
            });

            it('has all required fields', function () {
              expect($ctrl.request.from_date).toBeDefined();
              expect($ctrl.request.to_date).toBeDefined();
              expect($ctrl.request.from_date_type).toBeDefined();
              expect($ctrl.request.to_date_type).toBeDefined();
              expect($ctrl.request.contact_id).toBeDefined();
              expect($ctrl.request.status_id).toBeDefined();
              expect($ctrl.request.type_id).toBeDefined();
            });

            it('is successful', function () {
              expect($ctrl.errors.length).toBe(0);
              expect($ctrl.request.id).toBeDefined();
            });

            it('allows user to submit', function () {
              expect($ctrl.canSubmit()).toBeTruthy();
            });

            it('calls corresponding API end points', function () {
              expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
              expect(LeaveRequestAPI.create).toHaveBeenCalled();
            });

            it('sends event', function () {
              expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::new', $ctrl.request);
            });
          });
        });

        describe('when absence period is changed', function () {
          describe('for multiple days', function () {
            describe('before from date is selected', function () {
              it('disables to date and to type', function () {
                expect($ctrl.request.from_date).toBeFalsy();
              });
            });

            describe('and after from date is selected', function () {
              beforeEach(function () {
                setTestDates(date2017);
              });

              it('enables to date and to type', function () {
                expect($ctrl.request.from_date).toBeTruthy();
              });

              it('checks if date is in any absence period without errors', function () {
                expect($ctrl.errors.length).toBe(0);
              });

              it('updates calendar', function () {
                expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
              });

              it('does not show balance', function () {
                expect($ctrl.uiOptions.showBalance).toBeFalsy();
              });

              describe('from available absence period', function () {
                var oldPeriodId;

                beforeEach(function () {
                  $ctrl.uiOptions.toDate = null;
                  oldPeriodId = $ctrl.period.id;
                  setTestDates(date2016);
                });

                it('changes absence period', function () {
                  expect($ctrl.period.id).not.toEqual(oldPeriodId);
                });

                it('sets min and max to date', function () {
                  expect($ctrl.uiOptions.date.to.options.minDate).not.toBeNull();
                  expect($ctrl.uiOptions.date.to.options.maxDate).not.toBeNull();
                });

                it('updates absence types from Entitlements', function () {
                  expect(EntitlementAPI.all).toHaveBeenCalled();
                });

                it('does not show balance', function () {
                  expect($ctrl.uiOptions.showBalance).toBeFalsy();
                });

                it('resets to date', function () {
                  expect($ctrl.request.to_date).toBeNull();
                });
              });

              describe('from unavailable absence period', function () {
                beforeEach(function () {
                  setTestDates(date2013);
                });

                it('shows error', function () {
                  expect($ctrl.errors).toEqual(jasmine.any(Array));
                });
              });

              describe('and to date is selected', function () {
                beforeEach(function () {
                  setTestDates(date2016, date2016);
                });

                it('selects date from selected absence period without errors', function () {
                  expect($ctrl.errors.length).toBe(0);
                });

                it('updates balance', function () {
                  expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
                });

                it('shows balance', function () {
                  expect($ctrl.uiOptions.showBalance).toBeTruthy();
                });
              });

              describe('and from date is changed after to date', function () {
                var from, to, minDate;

                beforeEach(function () {
                  setTestDates(date2016);
                  minDate = moment(getUTCDate(date2016)).add(1, 'd').toDate();
                });

                it('sets min date to from date', function () {
                  expect($ctrl.uiOptions.date.to.options.minDate).toEqual(minDate);
                });

                it('sets init date to from date', function () {
                  expect($ctrl.uiOptions.date.to.options.initDate).toEqual(minDate);
                });

                describe('and from date is less than to date', function () {
                  beforeEach(function () {
                    from = '9/12/2016';
                    to = '10/12/2016';

                    setTestDates(null, to);
                    setTestDates(from);
                  });

                  it('does not reset to date to equal from date', function () {
                    expect($ctrl.request.to_date).not.toEqual($ctrl.request.from_date);
                  });
                });

                describe('and from date is greater than to date', function () {
                  beforeEach(function () {
                    from = '11/12/2016';
                    to = '10/12/2016';

                    setTestDates(null, to);
                    setTestDates(from);
                  });

                  it('changes to date to equal to date', function () {
                    expect($ctrl.request.to_date).toEqual($ctrl.request.from_date);
                  });
                });
              });
            });
          });
        });

        describe('when user edits leave request', function () {
          describe('without comments', function () {
            beforeEach(function () {
              var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
              var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));
              leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
              leaveRequest.fileUploader.queue = [];
              var directiveOptions = {
                contactId: leaveRequest.contact_id, // staff's contact id
                isSelfRecord: true,
                leaveRequest: leaveRequest
              };

              initTestController(directiveOptions);
            });

            describe('on initialization', function () {
              var waitingApprovalStatus;

              beforeEach(function () {
                waitingApprovalStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
              });

              it('sets role to staff', function () {
                expect($ctrl.isRole('staff')).toBeTruthy();
              });

              it('sets mode to edit', function () {
                expect($ctrl.isMode('edit')).toBeTruthy();
              });

              it('sets all leaverequest values', function () {
                expect($ctrl.request.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
                expect($ctrl.request.type_id).toEqual('1');
                expect($ctrl.request.status_id).toEqual(waitingApprovalStatus.value);
                expect($ctrl.request.from_date).toEqual('2016-11-23');
                expect($ctrl.request.from_date_type).toEqual('1');
                expect($ctrl.request.to_date).toEqual('2016-11-28');
                expect($ctrl.request.to_date_type).toEqual('1');
              });

              it('does not allow user to submit', function () {
                expect($ctrl.canSubmit()).toBeFalsy();
              });

              it('does show balance', function () {
                expect($ctrl.uiOptions.showBalance).toBeTruthy();
              });

              it('loads day types', function () {
                expect($ctrl.requestFromDayTypes).toBeDefined();
                expect($ctrl.requestToDayTypes).toBeDefined();
              });
            });

            describe('and submits', function () {
              beforeEach(function () {
                spyOn($rootScope, '$emit');
                spyOn($ctrl.request, 'update').and.callThrough();
                // change date to enable submit button
                setTestDates(date2016);

                // entitlements are randomly generated so resetting them to positive here
                if ($ctrl.balance.closing < 0) {
                  $ctrl.balance.closing = 5;
                }

                $ctrl.submit();
                $scope.$apply();
              });

              it('allows user to submit', function () {
                expect($ctrl.canSubmit()).toBeTruthy();
              });

              it('calls appropriate API endpoint', function () {
                expect($ctrl.request.update).toHaveBeenCalled();
              });

              it('sends edit event', function () {
                expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::edit', $ctrl.request);
              });

              it('has no error', function () {
                expect($ctrl.errors.length).toBe(0);
              });

              it('closes model popup', function () {
                expect(modalInstanceSpy.close).toHaveBeenCalled();
              });
            });

            describe('user selects same from and to date', function () {
              beforeEach(function () {
                var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
                var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

                leaveRequest.from_date = leaveRequest.to_date = dateServer2017;
                leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
                var directiveOptions = {
                  contactId: leaveRequest.contact_id, // staff's contact id
                  isSelfRecord: true,
                  leaveRequest: leaveRequest
                };

                initTestController(directiveOptions);
              });

              it('selects single day', function () {
                expect($ctrl.uiOptions.multipleDays).toBeFalsy();
              });
            });

            describe('manager asks for more information', function () {
              var expectedStatusValue;

              beforeEach(function () {
                var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '4');
                var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

                leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
                var directiveOptions = {
                  contactId: leaveRequest.contact_id, // staff's contact id
                  isSelfRecord: true,
                  leaveRequest: leaveRequest
                };

                initTestController(directiveOptions);
                expectedStatusValue = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
                $ctrl.balance.closing = 5;
                $ctrl.submit();
              });

              it('status changes to waiting approval before calling API', function () {
                expect($ctrl.request.status_id).toEqual(expectedStatusValue);
              });
            });

            describe('user adds comments', function () {
              beforeEach(function () {
                $ctrl.request.comments = [];
                $ctrl.request.comments.push({
                  contact_id: '101',
                  leave_request_id: '102',
                  text: 'some text'
                });
              });

              it('allows user to submit', function () {
                expect($ctrl.canSubmit()).toBeTruthy();
              });
            });
          });
        });

        describe('canSubmit()', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            leaveRequest.fileUploader.queue = [];

            initTestController({
              contactId: leaveRequest.contact_id, // staff's contact id
              isSelfRecord: true,
              leaveRequest: leaveRequest
            });
          });

          it('does not allow to submit the leave request without changes', function () {
            expect($ctrl.canSubmit()).toBe(false);
          });

          describe('when a comment is added', function () {
            beforeEach(function () {
              $ctrl.request.comments.push(jasmine.any(Object));
            });

            it('allows to submit the leave request', function () {
              expect($ctrl.canSubmit()).toBe(true);
            });
          });
        });

        describe('in view mode', function () {
          var leaveRequest;

          beforeEach(function () {
            var approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
            leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', approvalStatus));
            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            var directiveOptions = {
              contactId: leaveRequest.contact_id, // staff's contact id
              isSelfRecord: true,
              leaveRequest: leaveRequest
            };

            initTestController(directiveOptions);
          });

          it('sets mode to view', function () {
            expect($ctrl.isMode('view')).toBeTruthy();
          });

          it('sets contact id', function () {
            expect($ctrl.request.contact_id).toEqual(leaveRequest.contact_id);
          });

          describe('on submit', function () {
            beforeEach(function () {
              spyOn($ctrl.request, 'update').and.callThrough();
              $ctrl.submit();
              $scope.$apply();
            });

            it('does not update leave request', function () {
              expect($ctrl.request.update).not.toHaveBeenCalled();
            });
          });
        });
      });

      describe('manager opens leave request popup', function () {
        beforeEach(function () {
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          var directiveOptions = {
            contactId: 203, // manager's contact id
            leaveRequest: leaveRequest
          };
          role = 'manager';

          initTestController(directiveOptions);
        });

        describe('on initialization', function () {
          var waitingApprovalStatus;

          beforeEach(function () {
            $ctrl.request.fileUploader.queue = [];
            waitingApprovalStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
          });

          it('sets the manager role', function () {
            expect($ctrl.isRole('manager')).toBeTruthy();
          });

          it('sets all leaverequest values', function () {
            expect($ctrl.request.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
            expect($ctrl.request.type_id).toEqual(jasmine.any(String));
            expect($ctrl.request.status_id).toEqual(waitingApprovalStatus.value);
            expect($ctrl.request.from_date).toEqual(jasmine.any(String));
            expect($ctrl.request.from_date_type).toEqual(jasmine.any(String));
            expect($ctrl.request.to_date).toEqual(jasmine.any(String));
            expect($ctrl.request.to_date_type).toEqual(jasmine.any(String));
          });

          it('gets contact name', function () {
            expect($ctrl.contactName).toEqual(jasmine.any(String));
          });

          it('does not allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeFalsy();
          });

          it('shows balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });

          it('loads day types', function () {
            expect($ctrl.requestFromDayTypes).toBeDefined();
            expect($ctrl.requestToDayTypes).toBeDefined();
          });
        });

        describe('on submit', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            spyOn($ctrl.request, 'update').and.callThrough();

            // entitlements are randomly generated so resetting them to positive here
            if ($ctrl.balance.closing < 0) {
              $ctrl.balance.closing = 0;
            }
            // set status id manually as manager would set it on UI
            $ctrl.newStatusOnSave = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
            $ctrl.submit();
            $scope.$apply();
          });

          it('allows user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('calls update method on instance', function () {
            expect($ctrl.request.update).toHaveBeenCalled();
          });

          it('calls corresponding API end points', function () {
            expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
            expect(LeaveRequestAPI.update).toHaveBeenCalled();
          });

          it('sends update event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::updatedByManager', $ctrl.request);
          });
        });

        describe('when the popup is closed', function () {
          beforeEach(function () {
            $ctrl.closeAlert();
          });

          it('flushes any current errors', function () {
            expect($ctrl.errors).toEqual([]);
          });
        });
      });

      describe('manager raises absence request on behalf of staff', function () {
        beforeEach(function () {
          var directiveOptions = {
            contactId: 203 // manager's contact id
          };
          role = 'manager';
          initTestController(directiveOptions);
        });

        it('does not set contact', function () {
          expect($ctrl.contactName).toBeNull();
        });

        it('does not initialize absence types', function () {
          expect(AbsenceTypeAPI.all).not.toHaveBeenCalled();
        });

        describe('after contact is selected', function () {
          describe('when entitlement is present', function () {
            var approvalStatus;

            beforeEach(function () {
              approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
              $ctrl.request.contact_id = 202;
              $ctrl.initAfterContactSelection();
              $scope.$digest();
            });

            it('sets manager role', function () {
              expect($ctrl.isRole('manager')).toBeTruthy();
            });

            it('sets create mode', function () {
              expect($ctrl.isMode('create')).toBeTruthy();
            });

            it('does not initialize absence types', function () {
              expect(AbsenceTypeAPI.all).toHaveBeenCalled();
            });

            it('sets status to approved', function () {
              expect($ctrl.newStatusOnSave).toEqual(approvalStatus);
            });

            describe('cancelled status', function () {
              var cancelStatus, availableStatuses;

              beforeEach(function () {
                cancelStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'cancelled');
                availableStatuses = $ctrl.getStatuses();
              });

              it('is not available', function () {
                expect(availableStatuses).not.toContain(cancelStatus);
              });
            });
          });
        });

        describe('after contact is deselected', function () {
          var promise;

          beforeEach(function () {
            $ctrl.request.contact_id = undefined;
            promise = $ctrl.initAfterContactSelection();
            $scope.$digest();
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('does not call calendar APIs', function () {
            expect(WorkPatternAPI.getCalendar).not.toHaveBeenCalled();
          });

          it('throws error', function () {
            promise.catch(function (err) {
              expect(err).toEqual('The contact id was not set');
            });
          });
        });
      });

      describe('admin opens leave request popup in edit mode', function () {
        var adminId = 206;

        beforeEach(function () {
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

          leaveRequest.contact_id = adminId.toString();

          role = 'admin';
          initTestController({
            contactId: adminId,
            leaveRequest: leaveRequest
          });
        });

        describe('on initialization', function () {
          it('is in edit mode', function () {
            expect($ctrl.isMode('edit')).toBeTruthy();
          });

          it('has admin role', function () {
            expect($ctrl.isRole('admin')).toBeTruthy();
          });

          it('does not load contacts', function () {
            expect($ctrl.managedContacts.length).toEqual(0);
          });

          describe('loading', function () {
            it('is not loading fromDayTypes', function () {
              expect($ctrl.loading.fromDayTypes).toBe(false);
            });

            it('is not loading toDayTypes', function () {
              expect($ctrl.loading.toDayTypes).toBe(false);
            });
          });
        });
      });

      describe('admin opens leave request popup in create mode', function () {
        var adminId = 206;

        beforeEach(function () {
          $ctrl.request.contact_id = adminId.toString();

          role = 'admin';
          initTestController({
            contactId: adminId
          });
        });

        describe('on initialization', function () {
          it('is in create mode', function () {
            expect($ctrl.isMode('create')).toBeTruthy();
          });

          it('has admin role', function () {
            expect($ctrl.isRole('admin')).toBeTruthy();
          });

          it('does not contain admin in the list of managees', function () {
            expect(_.find($ctrl.managedContacts, { 'id': adminId })).toBeUndefined();
          });
        });
      });

      describe('admin opens leave request popup in create mode for a pre-selected contact', function () {
        var selectedContactId = 208;
        var adminId = 206;

        beforeEach(function () {
          $ctrl.request.contact_id = adminId.toString();

          role = 'admin';
          initTestController({
            contactId: adminId,
            selectedContactId: selectedContactId
          });
        });

        describe('on initialization', function () {
          it('is in create mode', function () {
            expect($ctrl.isMode('create')).toBeTruthy();
          });

          it('has admin role', function () {
            expect($ctrl.isRole('admin')).toBeTruthy();
          });

          it('has pre-selected contact id', function () {
            expect($ctrl.request.contact_id).toEqual(selectedContactId);
          });

          it('loads exactly one contact', function () {
            expect($ctrl.managedContacts.length).toEqual(1);
          });
        });
      });

      describe('deleteLeaveRequest()', function () {
        var confirmFunction;

        beforeEach(function () {
          spyOn(dialog, 'open').and.callFake(function (params) {
            confirmFunction = params.onConfirm;
          });
          initTestController({ contactId: CRM.vars.leaveAndAbsences.contactId });
          $ctrl.deleteLeaveRequest();
        });

        it('confirms before deleting the leave request', function () {
          expect(dialog.open).toHaveBeenCalled();
        });

        describe('when deletion is confirmed', function () {
          var promise;

          beforeEach(function () {
            spyOn($ctrl, 'dismissModal');
            spyOn($rootScope, '$emit');
            $ctrl.directiveOptions.leaveRequest = jasmine.createSpyObj(['delete']);
            $ctrl.directiveOptions.leaveRequest.delete.and.returnValue($q.resolve([]));
            promise = confirmFunction();
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('deletes the leave request', function () {
            expect($ctrl.directiveOptions.leaveRequest.delete).toHaveBeenCalled();
          });

          it('closes the leave modal', function () {
            promise.then(function () {
              expect($ctrl.dismissModal).toHaveBeenCalled();
            });
          });

          it('publishes a delete event', function () {
            promise.then(function () {
              expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::deleted', $ctrl.directiveOptions.leaveRequest);
            });
          });
        });
      });

      describe('getStatuses', function () {
        var status = {};

        beforeEach(function () {
          var collectionName = 'hrleaveandabsences_leave_request_status';
          var allStatuses = optionGroupMock.getCollection(collectionName);

          status = _.indexBy(allStatuses, 'name');

          initTestController({
            contactId: CRM.vars.leaveAndAbsences.contactId,
            userRole: 'admin'
          });

          $ctrl._init();
          $ctrl.request = { status_id: null };
          $ctrl.requestStatuses = status;
        });

        describe('when request is not defined', function () {
          beforeEach(function () {
            delete $ctrl.request;
          });

          it('returns an empty array', function () {
            expect($ctrl.getStatuses()).toEqual([]);
          });
        });

        describe('when requestStatuses is empty', function () {
          beforeEach(function () {
            $ctrl.requestStatuses = {};
          });

          it('returns an empty array', function () {
            expect($ctrl.getStatuses()).toEqual([]);
          });
        });

        describe('when previous status was not defined', function () {
          it('returns *More Information Required, Approve* ', function () {
            expect($ctrl.getStatuses()).toEqual([
              status.more_information_required,
              status.approved
            ]);
          });
        });

        describe('when previous status is *Awaiting Approval*', function () {
          beforeEach(function () {
            $ctrl.request.status_id = status.awaiting_approval.value;
          });

          it('returns *More Information Required, Approve, Reject, Cancel*', function () {
            expect($ctrl.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *More Information Required*', function () {
          beforeEach(function () {
            $ctrl.request.status_id = status.more_information_required.value;
          });

          it('returns *Approve, More Information Required, Reject, Cancel* ', function () {
            expect($ctrl.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *Rejected*', function () {
          beforeEach(function () {
            $ctrl.request.status_id = status.rejected.value;
          });

          it('returns *Approve, More Information Required, Reject, Cancel*', function () {
            expect($ctrl.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *Approved*', function () {
          beforeEach(function () {
            $ctrl.request.status_id = status.approved.value;
          });

          it('returns *Approve, More Information Required, Reject, Cancel* ', function () {
            expect($ctrl.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *Cancelled*', function () {
          beforeEach(function () {
            $ctrl.request.status_id = status.cancelled.value;
          });

          it('returns *Awaiting Approval , Approve, More Information Required, Reject, Cancel* ', function () {
            expect($ctrl.getStatuses()).toEqual([
              status.awaiting_approval,
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });
      });

      /**
       * Initialize the controller
       *
       * @param leave request
       */
      function initTestController (directiveOptions) {
        $scope = $rootScope.$new();

        $ctrl = $controller('LeaveRequestCtrl', {
          $scope: $scope,
          $uibModalInstance: modalInstanceSpy,
          directiveOptions: directiveOptions
        });

        $scope.$digest();
      }

      /**
       * sets from and/or to dates
       * @param {String} from date set if passed
       * @param {String} to date set if passed
       */
      function setTestDates (from, to) {
        if (from) {
          $ctrl.uiOptions.fromDate = getUTCDate(from);
          $ctrl.updateAbsencePeriodDatesTypes($ctrl.uiOptions.fromDate, 'from');
          $scope.$digest();
        }

        if (to) {
          $ctrl.uiOptions.toDate = getUTCDate(to);
          $ctrl.updateAbsencePeriodDatesTypes($ctrl.uiOptions.toDate, 'to');
          $scope.$digest();
        }
      }

      function getUTCDate (date) {
        var now = new Date(date);
        return new Date(now.getTime() + now.getTimezoneOffset() * 60000);
      }
    });
  });
})(CRM);
