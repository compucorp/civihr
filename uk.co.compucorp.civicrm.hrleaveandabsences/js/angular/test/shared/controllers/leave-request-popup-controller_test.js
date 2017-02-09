(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'common/angular',
    'mocks/data/option-group-mock-data',
    'mocks/data/leave-request-data',
    'mocks/helpers/helper',
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
    'common/mocks/services/api/contact-mock',
    'leave-absences/shared/controllers/leave-request-popup-controller',
    'leave-absences/shared/modules/shared-settings',
  ], function (_, moment, angular, optionGroupMock, mockData, helper) {
    'use strict';

    describe('LeaveRequestPopupCtrl', function () {
      var $log, $rootScope, $ctrl, modalInstanceSpy, $scope, $q, $controller,
        $provide, sharedSettings, AbsenceTypeAPI, LeaveRequestInstance, Contact, ContactAPIMock,
        EntitlementAPI, LeaveRequestAPI, SicknessRequestInstance, WorkPatternAPI,
        date2016 = '01/12/2016',
        date2017 = '02/02/2017',
        date2013 = '02/02/2013';

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks', 'leave-absences.settings',
        function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(function (_AbsencePeriodAPIMock_, _HR_settingsMock_,
        _AbsenceTypeAPIMock_, _EntitlementAPIMock_, _WorkPatternAPIMock_,
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
        $provide.value('HR_settings', _HR_settingsMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      }));

      beforeEach(inject(['api.contact.mock', 'shared-settings', function (_ContactAPIMock_, _sharedSettings_) {
        $provide.value('api.contact', _ContactAPIMock_);
        sharedSettings = _sharedSettings_;
      }]));

      beforeEach(inject(function (_$log_, _$controller_, _$rootScope_, _$q_,
        _AbsenceTypeAPI_, _Contact_, _EntitlementAPI_, _LeaveRequestInstance_,
        _LeaveRequestAPI_, _SicknessRequestInstance_, _WorkPatternAPI_) {

        $log = _$log_;
        $rootScope = _$rootScope_;
        $controller = _$controller_;
        $q = _$q_;
        Contact = _Contact_;
        EntitlementAPI = _EntitlementAPI_;
        LeaveRequestAPI = _LeaveRequestAPI_;
        WorkPatternAPI = _WorkPatternAPI_;
        AbsenceTypeAPI = _AbsenceTypeAPI_;

        LeaveRequestInstance = _LeaveRequestInstance_;
        SicknessRequestInstance = _SicknessRequestInstance_;

        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });

        spyOn(AbsenceTypeAPI, 'all').and.callThrough();
        spyOn(EntitlementAPI, 'all').and.callThrough();
        spyOn(LeaveRequestAPI, 'calculateBalanceChange').and.callThrough();
        spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
        spyOn(SicknessRequestInstance, 'init').and.callThrough();

        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);
      }));

      beforeEach(inject(function () {
        var directiveOptions = {
          contactId: CRM.vars.leaveAndAbsences.contactId
        };

        initTestController(directiveOptions);
      }));

      it('is called', function () {
        expect($log.debug).toHaveBeenCalled();
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
            expect($ctrl.leaveRequest.type_id).toEqual($ctrl.absenceTypes[0].id);
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

          it('gets absence types with false sick param', function() {
            expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({ is_sick: false })
          });

          describe('leave request instance', function () {
            it('has new instance created', function () {
              expect($ctrl.leaveRequest).toEqual(jasmine.any(Object));
            });

            it('has contact_id set', function () {
              expect($ctrl.leaveRequest.contact_id).toBeDefined();
            });

            it('does not have from/to dates set', function () {
              expect($ctrl.leaveRequest.from_date).not.toBeDefined();
              expect($ctrl.leaveRequest.to_date).not.toBeDefined();
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
            expect($ctrl.leaveRequest.from_date).toEqual(fromDate);
          });

          it('selects first day type', function () {
            expect($ctrl.leaveRequest.from_date_type).toEqual('1');
          });
        });

        describe('after to date is selected', function () {
          var toDate;

          beforeEach(function () {
            setTestDates(date2016, date2016);
            toDate = moment($ctrl.uiOptions.toDate).format(sharedSettings.serverDateFormat);
          });

          it('sets to date', function () {
            expect($ctrl.leaveRequest.to_date).toEqual(toDate);
          });

          it('select first day type', function () {
            expect($ctrl.leaveRequest.to_date_type).toEqual('1');
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

      describe('when user cancels dialog (clicks X), or back button', function () {
        beforeEach(function () {
          $ctrl.cancel();
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
            $ctrl.leaveRequest.type_id = $ctrl.absenceTypes[1].id;
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

      describe('number of days selection', function () {
        describe('when switching to single day', function () {
          beforeEach(function () {
            $ctrl.uiOptions.multipleDays = false;
            $ctrl.changeInNoOfDays();
            $scope.$digest();
          });

          it('hides to date and type', function () {
            expect($ctrl.uiOptions.toDate).toBeNull();
            expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
          });

          it('resets balance and types', function () {
            expect($ctrl.balance.closing).toEqual(0);
            expect($ctrl.balance.change.amount).toEqual(0);
          });

          it('shows no balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeFalsy();
          });

          describe('after from date is selected', function () {
            beforeEach(function () {
              setTestDates(date2016);
            });

            it('sets from and to dates', function () {
              expect($ctrl.leaveRequest.from_date).not.toBeNull();
              expect($ctrl.leaveRequest.to_date).not.toBeNull();
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
            expect(moment($ctrl.leaveRequest.from_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
          });
        });

        describe('when to date is selected', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016);
          });

          it('sets to date', function () {
            expect(moment($ctrl.leaveRequest.to_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
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
            expect($ctrl.leaveRequest.to_date_type).toEqual(expectedDayType);
          });
        });

        describe('when from and to are selected', function () {
          beforeEach(function () {
            spyOn($ctrl, 'calculateBalanceChange').and.callThrough();
            setTestDates(date2016, date2016);
          });

          it('calculates balance change', function () {
            expect($ctrl.calculateBalanceChange).toHaveBeenCalled();
          });
        });
      });

      describe('calculate balance', function () {
        describe('when day type changed', function () {
          describe('for single day', function () {
            beforeEach(function () {
              //select half_day_am  to get single day mock data
              $ctrl.leaveRequest.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'half_day_am');
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
              //select all_day to get multiple day mock data
              setTestDates(date2016, date2016);
              $ctrl.leaveRequest.from_date_type = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');
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
        describe('when submit with invalid fields', function () {
          beforeEach(function () {
            $ctrl.submit();
            $scope.$digest();
          });

          it('fails with error', function () {
            expect($ctrl.error).toEqual(jasmine.any(Object));
          });

          it('does not allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeFalsy();
          });
        });

        describe('when submit with valid fields', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            setTestDates(date2016, date2016);
            //entitlements are randomly generated so resetting them to positive here
            $ctrl.balance.closing = 1;
            $ctrl.submit();
            $scope.$digest();
          });

          it('has all required fields', function () {
            expect($ctrl.leaveRequest.from_date).toBeDefined();
            expect($ctrl.leaveRequest.to_date).toBeDefined();
            expect($ctrl.leaveRequest.from_date_type).toBeDefined();
            expect($ctrl.leaveRequest.to_date_type).toBeDefined();
            expect($ctrl.leaveRequest.contact_id).toBeDefined();
            expect($ctrl.leaveRequest.status_id).toBeDefined();
            expect($ctrl.leaveRequest.type_id).toBeDefined();
          });

          it('is successful', function () {
            expect($ctrl.error).toBeNull();
            expect($ctrl.leaveRequest.id).toBeDefined();
          });

          it('allows user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('sends event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::new', $ctrl.leaveRequest);
          });

          describe('when balance change is negative', function () {
            beforeEach(function () {
              $ctrl.selectedAbsenceType = $ctrl.absenceTypes[1];
              setTestDates(date2016, date2016);
              //entitlements are randomly generated so resetting them to negative here
              $ctrl.balance.closing = -1;
              $ctrl.submit();
              $scope.$digest();
            });

            describe('and absence type does not allow overuse', function () {
              it('does not save and sets error', function () {
                expect($ctrl.error).toBeDefined();
              });
            });

            describe('and absence type allows overuse', function () {
              beforeEach(function () {
                $ctrl.leaveRequest.type_id = $ctrl.absenceTypes[2].id;
                $ctrl.updateBalance();
                $ctrl.submit();
                $scope.$digest();
              });

              it('saves without errors', function () {
                expect($ctrl.error).toBeNull();
              });
            });
          });
        });
      });

      describe('when manager opens leave request popup', function () {
        beforeEach(function () {
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          var directiveOptions = {
            contactId: 203, //manager's contact id
            leaveRequest: leaveRequest
          };

          initTestController(directiveOptions);
        });

        describe('on initialization', function () {
          var waitingApprovalStatus;

          beforeEach(function () {
            waitingApprovalStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
          });

          it('sets the manager role', function () {
            expect($ctrl.isRole('manager')).toBeTruthy();
          });

          it('sets all leaverequest values', function () {
            expect($ctrl.leaveRequest.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
            expect($ctrl.leaveRequest.type_id).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.status_id).toEqual(waitingApprovalStatus.value);
            expect($ctrl.leaveRequest.from_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.from_date_type).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date_type).toEqual(jasmine.any(String));
          });

          it('gets contact name', function () {
            expect($ctrl.contact.display_name).toEqual(jasmine.any(String));
          });

          it('does not allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeFalsy();
          });

          it('shows balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });

          it('loads day types', function () {
            expect($ctrl.leaveRequestFromDayTypes).toBeDefined();
            expect($ctrl.leaveRequestToDayTypes).toBeDefined();
          });
        });

        describe('on submit', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            spyOn($ctrl.leaveRequest, 'update').and.callThrough();

            //entitlements are randomly generated so resetting them to positive here
            if ($ctrl.balance.closing < 0) {
              $ctrl.balance.closing = 0;
            }
            //set status id manually as manager would set it on UI
            $ctrl.leaveRequest.status_id = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
            $ctrl.submit();
            $scope.$apply();
          });

          it('allows user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('calls expected api', function () {
            expect($ctrl.leaveRequest.update).toHaveBeenCalled();
          });

          it('sends update event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::updatedByManager', $ctrl.leaveRequest);
          });
        });
      });

      describe('when absence period is changed', function () {
        describe('for multiple days', function () {
          describe('before from date is selected', function () {
            it('disables to date and to type', function () {
              expect($ctrl.leaveRequest.from_date).toBeFalsy();
            });
          });

          describe('after from date is selected', function () {
            beforeEach(function () {
              setTestDates(date2017);
            });

            it('enables to date and to type', function () {
              expect($ctrl.leaveRequest.from_date).toBeTruthy();
            });

            it('checks if date is in any absence period without errors', function () {
              expect($ctrl.error).toBeNull();
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
                expect($ctrl.leaveRequest.to_date).toBeNull();
              });
            });

            describe('from unavailable absence period', function () {
              beforeEach(function () {
                setTestDates(date2013);
              });

              it('shows error', function () {
                expect($ctrl.error).toEqual(jasmine.any(String));
              });
            });

            describe('and to date is selected', function () {
              beforeEach(function () {
                setTestDates(date2016, date2016);
              });

              it('selects date from selected absence period without errors', function () {
                expect($ctrl.error).toBeNull();
              });

              it('updates balance', function () {
                expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
              });

              it('shows balance', function () {
                expect($ctrl.uiOptions.showBalance).toBeTruthy();
              });
            });

            describe('from date is changed after to date', function () {
              var from, to;

              beforeEach(function () {
                setTestDates(date2016);
              });

              it('sets min date to from date', function () {
                expect($ctrl.uiOptions.date.to.options.minDate).toEqual(new Date(date2016));
              });

              describe('and from date is less than to date', function () {
                beforeEach(function () {
                  from = '9/12/2016', to = '10/12/2016';

                  setTestDates(null, to);
                  setTestDates(from);
                });

                it('does not reset to date to equal from date', function () {
                  expect($ctrl.leaveRequest.to_date).not.toEqual($ctrl.leaveRequest.from_date);
                });
              });

              describe('and from date is greater than to date', function () {
                beforeEach(function () {
                  from = '11/12/2016', to = '10/12/2016';

                  setTestDates(null, to);
                  setTestDates(from);
                });

                it('changes to date to equal to date', function () {
                  expect($ctrl.leaveRequest.to_date).toEqual($ctrl.leaveRequest.from_date);
                });
              });
            });
          });
        });
      });

      describe('when user edits leave request', function () {
        beforeEach(function () {
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          var directiveOptions = {
            contactId: leaveRequest.contact_id, //owner's contact id
            leaveRequest: leaveRequest
          };

          initTestController(directiveOptions);
        });

        describe('on initialization', function () {
          var waitingApprovalStatus;

          beforeEach(function () {
            waitingApprovalStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
          });

          it('sets role to owner', function () {
            expect($ctrl.isRole('owner')).toBeTruthy();
          });

          it('sets mode to edit', function() {
            expect($ctrl.isMode('edit')).toBeTruthy();
          });

          it('sets all leaverequest values', function () {
            expect($ctrl.leaveRequest.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
            expect($ctrl.leaveRequest.type_id).toEqual('1');
            expect($ctrl.leaveRequest.status_id).toEqual(waitingApprovalStatus.value);
            expect($ctrl.leaveRequest.from_date).toEqual('2016-11-23');
            expect($ctrl.leaveRequest.from_date_type).toEqual('1');
            expect($ctrl.leaveRequest.to_date).toEqual('2016-11-28');
            expect($ctrl.leaveRequest.to_date_type).toEqual('1');
          });

          it('does not allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeFalsy();
          });

          it('does show balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });

          it('loads day types', function () {
            expect($ctrl.leaveRequestFromDayTypes).toBeDefined();
            expect($ctrl.leaveRequestToDayTypes).toBeDefined();
          });
        });

        describe('and submits', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            spyOn($ctrl.leaveRequest, 'update').and.callThrough();
            //change date to enable submit button
            setTestDates(date2016);

            //entitlements are randomly generated so resetting them to positive here
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
            expect($ctrl.leaveRequest.update).toHaveBeenCalled();
          });

          it('sends edit event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::edit', $ctrl.leaveRequest);
          });

          it('has no error', function () {
            expect($ctrl.error).toBeNull();
          });

          it('closes model popup', function () {
            expect(modalInstanceSpy.close).toHaveBeenCalled();
          });
        });
      });

      describe('user opens in view mode', function() {
        beforeEach(function() {
          var approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', approvalStatus));
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          var directiveOptions = {
            contactId: leaveRequest.contact_id, //owner's contact id
            leaveRequest: leaveRequest
          };

          initTestController(directiveOptions);
        });

        it('sets mode to view', function() {
          expect($ctrl.isMode('view')).toBeTruthy();
        });

        describe('on submit', function() {
          beforeEach(function() {
            spyOn($ctrl.leaveRequest, 'update').and.callThrough();
            $ctrl.submit();
            $scope.$apply()
          });

          it('does not update leave request', function () {
            expect($ctrl.leaveRequest.update).not.toHaveBeenCalled();
          });
        });
      });

      describe('sick request', function() {
        beforeEach(function() {
          var directiveOptions = {
            contactId: CRM.vars.leaveAndAbsences.contactId,
            leaveType: 'sick'
          };

          initTestController(directiveOptions);
        });

        it('has leave type set to sick', function () {
          expect($ctrl.isLeaveType('sick')).toBeTruthy();
        });

        it('calls init on sickness instance', function() {
          expect(SicknessRequestInstance.init).toHaveBeenCalledWith({contact_id: CRM.vars.leaveAndAbsences.contactId});
        });

        it('loads reasons option types', function() {
          expect(Object.keys($ctrl.sicknessReasons).length).toBeGreaterThan(0);
        });

        it('loads documents option types', function() {
          expect($ctrl.sicknessDocumentTypes.length).toBeGreaterThan(0);
        });

        it('gets absence types with true sick param', function() {
          expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({ is_sick: true })
        });

        it('cannot submit request', function() {
          expect($ctrl.canSubmit()).toBe(false);
        });

        describe('with selected reason', function() {
          beforeEach(function() {
            var reason = optionGroupMock.specificObject('hrleaveandabsences_sickness_reason', 'name', 'appointment');

            $ctrl.leaveRequest = helper.createRandomSicknessRequest();
            $ctrl.leaveRequest.reason = reason.value;
          });

          it('cannot submit request', function() {
            expect($ctrl.canSubmit()).toBe(true);
          });

          describe('when user changes number of days selected', function() {
            beforeEach(function() {
              $ctrl.changeInNoOfDays();
            });

            it('resets reason', function() {
              expect($ctrl.leaveRequest.reason).toBeNull();
            });
          });
        });

        describe('with required documents', function() {
          describe('not added', function() {
            it('is not defined', function() {
              expect($ctrl.leaveRequest.required_documents).not.toBeDefined();
            });
          });

          describe('added', function() {
            beforeEach(function () {
              $ctrl.updateRequiredDocuments("1");
            });

            it('has required documents', function() {
              expect($ctrl.leaveRequest.required_documents).toEqual(['1']);
            });
          });

          describe('removed', function() {
            beforeEach(function () {
              //user added by selecting
              $ctrl.updateRequiredDocuments("1");
              //user removed by unselecting
              $ctrl.updateRequiredDocuments("1");
            });

            it('has no required documents', function() {
              expect($ctrl.leaveRequest.required_documents).toEqual([]);
            });
          });
        });
      });

      /**
       * Initialize the controller
       *
       * @param leave request
       */
      function initTestController(directiveOptions) {
        $scope = $rootScope.$new();

        $ctrl = $controller('LeaveRequestPopupCtrl', {
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
      function setTestDates(from, to) {
        if (from) {
          $ctrl.uiOptions.fromDate = new Date(from);
          $ctrl.updateAbsencePeriodDatesTypes($ctrl.uiOptions.fromDate, 'from');
          $scope.$digest();
        }

        if (to) {
          $ctrl.uiOptions.toDate = new Date(to);
          $ctrl.updateAbsencePeriodDatesTypes($ctrl.uiOptions.toDate, 'to');
          $scope.$digest();
        }
      }
    });
  });
})(CRM);
