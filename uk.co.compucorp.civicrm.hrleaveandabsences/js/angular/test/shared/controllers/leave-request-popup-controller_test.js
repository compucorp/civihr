(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'common/angular',
    'mocks/data/option-group-mock-data',
    'mocks/data/leave-request-data',
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
  ], function (_, moment, angular, optionGroupMock, mockData) {
    'use strict';

    describe('LeaveRequestPopupCtrl', function () {
      var $log, $rootScope, $ctrl, modalInstanceSpy, $scope, $q, $controller,
        $provide, DateFormat, LeaveRequestInstance, Contact, ContactAPIMock,
        EntitlementAPI, LeaveRequestAPI, WorkPatternAPI,
        serverDateFormat = 'YYYY-MM-DD',
        date2016 = '01/12/2016',
        date2017 = '02/02/2017',
        date2013 = '02/02/2013';

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks',
        function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(function (_AbsencePeriodAPIMock_, _HR_settingsMock_,
        _AbsenceTypeAPIMock_, _EntitlementAPIMock_, _WorkPatternAPI_,
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPI_);
        $provide.value('HR_settings', _HR_settingsMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      }));

      beforeEach(inject(['api.contact.mock', function (_ContactAPIMock_) {
        $provide.value('api.contact', _ContactAPIMock_);
      }]));

      beforeEach(inject(function (_$log_, _$controller_, _$rootScope_, _$q_,
        _LeaveRequestInstance_, _Contact_, _EntitlementAPI_, _LeaveRequestAPI_,
        _WorkPatternAPI_) {

        $log = _$log_;
        $rootScope = _$rootScope_;
        $controller = _$controller_;
        $q = _$q_;
        Contact = _Contact_;
        EntitlementAPI = _EntitlementAPI_;
        LeaveRequestAPI = _LeaveRequestAPI_;
        WorkPatternAPI = _WorkPatternAPI_;

        LeaveRequestInstance = _LeaveRequestInstance_;
        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });
        spyOn(EntitlementAPI, 'all').and.callThrough();
        spyOn(LeaveRequestAPI, 'calculateBalanceChange').and.callThrough();
        spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
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

          it('has no balance to show', function () {
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
            it('should be selected by default', function () {
              expect($ctrl.uiOptions.multipleDays).toBeTruthy();
            });
          });
        });

        describe('after from date is selected', function () {
          var fromDate;

          beforeEach(function () {
            setTestDates(date2016);
            fromDate = moment($ctrl.uiOptions.fromDate).format(serverDateFormat);
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

          it('will select first day type', function () {
            expect($ctrl.leaveRequest.from_date_type).toEqual('all_day');
          });
        });

        describe('after to date is selected', function () {
          var toDate;

          beforeEach(function () {
            setTestDates(date2016, date2016);
            toDate = moment($ctrl.uiOptions.toDate).format(serverDateFormat);
          });

          it('will set to date', function () {
            expect($ctrl.leaveRequest.to_date).toEqual(toDate);
          });

          it('will select first day type', function () {
            expect($ctrl.leaveRequest.to_date_type).toEqual('all_day');
          });
        });

        describe('from and to dates are selected', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016);
          });

          it('will show balance change', function () {
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

          it('should select another absence type', function () {
            expect(beforeChangeAbsenceType.id).not.toEqual(afterChangeAbsenceType.id);
          });

          it('should update balance', function () {
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

          it('will hide to date and type', function () {
            expect($ctrl.uiOptions.toDate).toBeNull();
            expect($ctrl.uiOptions.selectedToType).not.toBeDefined();
          });

          it('will reset balance and types', function () {
            expect($ctrl.balance.closing).toEqual(0);
            expect($ctrl.balance.change.amount).toEqual(0);
          });

          it('should not show balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeFalsy();
          });

          describe('after from date is selected', function () {
            beforeEach(function () {
              setTestDates(date2016);
            });

            it('should set from and to dates', function () {
              expect($ctrl.leaveRequest.from_date).not.toBeNull();
              expect($ctrl.leaveRequest.to_date).not.toBeNull();
            });

            it('should show balance', function () {
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

          it('will set from date', function () {
            expect(moment($ctrl.leaveRequest.from_date, serverDateFormat, true).isValid()).toBe(true);
          });
        });

        describe('when to date is selected', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016);
          });

          it('will set to date', function () {
            expect(moment($ctrl.leaveRequest.to_date, serverDateFormat, true).isValid()).toBe(true);
          });
        });
      });

      describe('day types', function () {
        describe('on change selection', function () {
          var expectedDayType;

          beforeEach(function () {
            expectedDayType = optionGroupMock.specificValue('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');
            setTestDates(null, date2016);
          });

          it('will select to date type', function () {
            expect($ctrl.leaveRequest.to_date_type).toEqual(expectedDayType);
          });
        });

        describe('when from and to are selected', function () {
          beforeEach(function () {
            spyOn($ctrl, 'calculateBalanceChange').and.callThrough();
            setTestDates(date2016, date2016);
          });

          it('will calculate balance change', function () {
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

            it('will update balance', function () {
              expect($ctrl.balance.change.amount).toEqual(jasmine.any(Number));
            });

            it('will update closing balance', function () {
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

            it('will update change amount', function () {
              expect($ctrl.balance.change.amount).toEqual(-2);
            });

            it('will update closing balance', function () {
              expect($ctrl.balance.closing).toEqual(jasmine.any(Number));
            });
          });
        });

        describe('when balance change is expanded during pagination', function () {
          beforeEach(function () {
            setTestDates(date2016, date2016);
          });

          it('will select default page', function () {
            expect($ctrl.pagination.currentPage).toEqual(1);
          });

          it('will set totalItems', function () {
            expect($ctrl.pagination.totalItems).toBeGreaterThan(0);
          });

          describe('when page selection changes', function () {
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

      describe('save leave request', function () {
        describe('when submit with invalid fields', function () {
          beforeEach(function () {
            $ctrl.submit();
            $scope.$digest();
          });

          it('should fail with error', function () {
            expect($ctrl.error).toEqual(jasmine.any(Object));
          });

          it('will not allow user to submit', function () {
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

          it('will allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('will send event', function () {
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
              it('will not save and set error', function () {
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

              it('will save without errors', function () {
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
          var waiting_approval;

          beforeEach(function () {
            waiting_approval = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
          });

          it('should set the manager role', function () {
            expect($ctrl.role).toEqual('manager');
          });

          it('should set all leaverequest values', function () {
            expect($ctrl.leaveRequest.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
            expect($ctrl.leaveRequest.type_id).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.status_id).toEqual(waiting_approval.value);
            expect($ctrl.leaveRequest.from_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.from_date_type).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date_type).toEqual(jasmine.any(String));
          });

          it('should get contact name', function () {
            expect($ctrl.contact.display_name).toEqual(jasmine.any(String));
          });

          it('will not allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeFalsy();
          });

          it('should show balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });

          it('should load day types', function () {
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

          it('will allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('calls expected api', function () {
            expect($ctrl.leaveRequest.update).toHaveBeenCalled();
          });

          it('will send update event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::updatedByManager', $ctrl.leaveRequest);
          });
        });
      });

      describe('when absence period is changed', function () {
        describe('for multiple days', function () {
          describe('before from date is selected', function () {
            it('should disable to date and to type', function () {
              expect($ctrl.leaveRequest.from_date).toBeFalsy();
            });
          });

          describe('after from date is selected', function () {
            beforeEach(function () {
              setTestDates(date2017);
            });

            it('should enable to date and to type', function () {
              expect($ctrl.leaveRequest.from_date).toBeTruthy();
            });

            it('should check if date is in any absence period without errors', function () {
              expect($ctrl.error).toBeNull();
            });

            it('should update calendar', function () {
              expect(WorkPatternAPI.getCalendar).toHaveBeenCalled();
            });

            it('should not show balance', function () {
              expect($ctrl.uiOptions.showBalance).toBeFalsy();
            });

            describe('from available absence period', function () {
              var oldPeriodId;

              beforeEach(function () {
                $ctrl.uiOptions.toDate = null;
                oldPeriodId = $ctrl.period.id;
                setTestDates(date2016);
              });

              it('should change absence period', function () {
                expect($ctrl.period.id).not.toEqual(oldPeriodId);
              });

              it('should set min and max to date', function () {
                expect($ctrl.uiOptions.date.to.options.minDate).not.toBeNull();
                expect($ctrl.uiOptions.date.to.options.maxDate).not.toBeNull();
              });

              it('should update absence types from Entitlements', function () {
                expect(EntitlementAPI.all).toHaveBeenCalled();
              });

              it('should not show balance', function () {
                expect($ctrl.uiOptions.showBalance).toBeFalsy();
              });

              it('should reset to date', function () {
                expect($ctrl.leaveRequest.to_date).toBeNull();
              });
            });

            describe('from unavailable absence period', function () {
              beforeEach(function () {
                setTestDates(date2013);
              });

              it('should show error', function () {
                expect($ctrl.error).toEqual(jasmine.any(String));
              });
            });

            describe('and to date is selected', function () {
              beforeEach(function () {
                setTestDates(date2016, date2016);
              });

              it('should select date from selected absence period without errors', function () {
                expect($ctrl.error).toBeNull();
              });

              it('should update balance', function () {
                expect(LeaveRequestAPI.calculateBalanceChange).toHaveBeenCalled();
              });

              it('should show balance', function () {
                expect($ctrl.uiOptions.showBalance).toBeTruthy();
              });
            });

            describe('from date is changed after to date', function () {
              var from, to;

              beforeEach(function () {
                setTestDates(date2016);
              });

              it('should set min date to from date', function () {
                expect($ctrl.uiOptions.date.to.options.minDate).toEqual(new Date(date2016));
              });

              describe('and from date is less than to date', function () {
                beforeEach(function () {
                  from = '9/12/2016', to = '10/12/2016';

                  setTestDates(null, to);
                  setTestDates(from);
                });

                it('should not reset to date to equal from date', function () {
                  expect($ctrl.leaveRequest.to_date).not.toEqual($ctrl.leaveRequest.from_date);
                });
              });

              describe('and from date is greater than to date', function () {
                beforeEach(function () {
                  from = '11/12/2016', to = '10/12/2016';

                  setTestDates(null, to);
                  setTestDates(from);
                });

                it('should change to date to equal to date', function () {
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
          var waiting_approval;

          beforeEach(function () {
            waiting_approval = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
          });

          it('should set role to owner', function () {
            expect($ctrl.role).toEqual('owner');
          });

          it('should set all leaverequest values', function () {
            expect($ctrl.leaveRequest.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
            expect($ctrl.leaveRequest.type_id).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.status_id).toEqual(waiting_approval.value);
            expect($ctrl.leaveRequest.from_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.from_date_type).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date_type).toEqual(jasmine.any(String));
          });

          it('will not allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeFalsy();
          });

          it('should show balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });

          it('should load day types', function () {
            expect($ctrl.leaveRequestFromDayTypes).toBeDefined();
            expect($ctrl.leaveRequestToDayTypes).toBeDefined();
          });
        });

        describe('and submits', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            spyOn($ctrl.leaveRequest, 'update').and.callThrough();

            //entitlements are randomly generated so resetting them to positive here
            if ($ctrl.balance.closing < 0) {
              $ctrl.balance.closing = 0;
            }
            //change date to enable submit button
            setTestDates(date2016);
            $ctrl.submit();
            $scope.$apply();
          });

          it('will allow user to submit', function () {
            expect($ctrl.canSubmit()).toBeTruthy();
          });

          it('should call appropriate API endpoint', function () {
            expect($ctrl.leaveRequest.update).toHaveBeenCalled();
          });

          it('should send edit event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::edit', $ctrl.leaveRequest);
          });

          it('should have no error', function () {
            expect($ctrl.error).toBeNull();
          });

          it('should close model popup', function () {
            expect(modalInstanceSpy.close).toHaveBeenCalled();
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
          $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
          $scope.$digest();
        }

        if (to) {
          $ctrl.uiOptions.toDate = new Date(to);
          $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
          $scope.$digest();
        }
      }
    });
  });
})(CRM);
