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
        serverDateFormat = 'YYYY-MM-DD';;

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
        //ContactAPIMock = _ContactAPIMock_;
        $provide.value('api.contact', _ContactAPIMock_);
      }]));

      beforeEach(inject(function (_$log_, _$controller_, _$rootScope_,
        _$q_, _LeaveRequestInstance_, _Contact_) {

        $log = _$log_;
        $rootScope = _$rootScope_;
        $controller = _$controller_;
        $q = _$q_;
        Contact = _Contact_;

        LeaveRequestInstance = _LeaveRequestInstance_;
        spyOn($log, 'debug');
        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });

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
            expect($ctrl.selectedAbsenceType).toEqual($ctrl.absenceTypes[0]);
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
            $ctrl.uiOptions.fromDate = new Date();
            fromDate = moment($ctrl.uiOptions.fromDate).format(serverDateFormat);
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
            $scope.$digest();
          });

          it('has balance change defined', function () {
            expect($ctrl.balance).toEqual(jasmine.any(Object));
            expect($ctrl.balance.opening).toEqual(jasmine.any(Number));
            expect($ctrl.balance.change).toEqual(jasmine.any(Object));
            expect($ctrl.balance.closing).toEqual(jasmine.any(Number));
          });

          it('has from day date and type defined', function () {
            expect($ctrl.leaveRequest.from_date).toEqual(fromDate);
            expect($ctrl.uiOptions.selectedFromType.name).toEqual('all_day');
            expect($ctrl.leaveRequest.from_date_type).toEqual('all_day');
          });

          it('will select first day type', function () {
            expect($ctrl.uiOptions.selectedFromType).toEqual($ctrl.leaveRequestFromDayTypes[0]);
          });
        });

        describe('after to date is selected', function () {
          var toDate;

          beforeEach(function () {
            $ctrl.uiOptions.toDate = new Date();
            toDate = moment($ctrl.uiOptions.toDate).format(serverDateFormat);
            $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
            $scope.$digest();
          });

          it('will set to day date and type', function () {
            expect($ctrl.leaveRequest.to_date).toEqual(toDate);
            expect($ctrl.uiOptions.selectedToType.name).toEqual('all_day');
            expect($ctrl.leaveRequest.to_date_type).toEqual('all_day');
          });

          it('will select first day type', function () {
            expect($ctrl.uiOptions.selectedToType).toEqual($ctrl.leaveRequestToDayTypes[0]);
          });
        });

        describe('from and to dates are selected', function () {
          beforeEach(function () {
            $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
            $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
            $scope.$digest();
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
            beforeChangeAbsenceType = $ctrl.selectedAbsenceType;
            $ctrl.selectedAbsenceType = $ctrl.absenceTypes[1];

            $ctrl.onAbsenceTypeChange();
            afterChangeAbsenceType = $ctrl.selectedAbsenceType;
            $scope.$digest();
          });

          it('should select another absence type', function () {
            expect(beforeChangeAbsenceType.id).not.toEqual(afterChangeAbsenceType.id);
          });

          it('should set type_id on leave request', function () {
            expect($ctrl.leaveRequest.type_id).toEqual($ctrl.selectedAbsenceType.id);
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
        describe('when from date is selected', function () {
          beforeEach(function () {
            $ctrl.onDateChange(new Date(), 'from');
            $scope.$digest();
          });

          it('will set from date', function () {
            expect(moment($ctrl.leaveRequest.from_date, serverDateFormat, true).isValid()).toBe(true);
          });
        });

        describe('when to date is selected', function () {
          beforeEach(function () {
            $ctrl.onDateChange(new Date(), 'to');
            $scope.$digest();
          });

          it('will set to date', function () {
            expect(moment($ctrl.leaveRequest.to_date, serverDateFormat, true).isValid()).toBe(true);
          });
        });
      });

      describe('day types', function () {
        describe('on change selection', function () {
          beforeEach(function () {
            $ctrl.onDateChange(new Date(), 'to');
            $scope.$digest();
          });

          it('will select to date type', function () {
            expect($ctrl.leaveRequest.to_date_type).toEqual($ctrl.uiOptions.selectedToType.name);
          });
        });

        describe('when from and to are selected', function () {
          beforeEach(function () {
            spyOn($ctrl, 'calculateBalanceChange').and.callThrough();
            $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
            $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
            $scope.$digest();
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
              $ctrl.uiOptions.selectedFromType = optionGroupMock.specificObject('hrleaveandabsences_leave_request_day_type', 'name', 'half_day_am');
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
              $ctrl.onDateChange(new Date(), 'from');
              $ctrl.onDateChange(new Date(), 'to');
              $ctrl.uiOptions.selectedFromType = optionGroupMock.specificObject('hrleaveandabsences_leave_request_day_type', 'name', 'all_day');
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
            $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
            $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
            $scope.$digest();
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

          it('should fail', function () {
            expect($ctrl.error).toEqual(jasmine.any(Object));
          });

        });

        describe('when submit with valid fields', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
            $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
            $scope.$digest();
            $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
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
            expect($ctrl.error).not.toBeDefined();
            expect($ctrl.leaveRequest.id).toBeDefined();
          });

          it('will send event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::new', $ctrl.leaveRequest);
          });

          describe('when balance change is negative', function () {
            beforeEach(function () {
              $ctrl.selectedAbsenceType = $ctrl.absenceTypes[1];
              $ctrl.uiOptions.toDate = $ctrl.uiOptions.fromDate = new Date();
              $ctrl.onDateChange($ctrl.uiOptions.fromDate, 'from');
              $ctrl.onDateChange($ctrl.uiOptions.toDate, 'to');
              $scope.$digest();
              //entitlements are randomly generated so resetting them to negative here
              $ctrl.balance.closing = -1;
              $ctrl.submit();
              $scope.$digest();
            });

            describe('and absence type does not allow overuse', function () {
              it('will not save', function () {
                expect($ctrl.error).toBeDefined();
              });
            });

            describe('and absence type allows overuse', function () {
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

      describe('when manager opens leave request popup', function () {
        beforeEach(function () {
          //waiting approval request at index 3 with value 3
          var leaveRequest = LeaveRequestInstance.init(mockData.all().values[3]);
          leaveRequest.contact_id = '' + CRM.vars.leaveAndAbsences.contactId;
          var directiveOptions = {
            contactId: 203, //manager's contact id
            leaveRequest: leaveRequest
          };

          initTestController(directiveOptions);
        });

        describe('initialized', function () {
          beforeEach(function () {
            $scope.$apply();
          });

          it('should allow to view staff details', function () {
            expect($ctrl.uiOptions.isManager).toBeTruthy();
          });

          it('should set all leaverequest values', function () {
            expect($ctrl.leaveRequest.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
            expect($ctrl.leaveRequest.type_id).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.status_id).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.from_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.from_date_type).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date).toEqual(jasmine.any(String));
            expect($ctrl.leaveRequest.to_date_type).toEqual(jasmine.any(String));
          });

          it('should get contact name', function () {
            expect($ctrl.uiOptions.contact.display_name).toEqual(jasmine.any(String));
          });
        });

        describe('on submit', function () {
          beforeEach(function () {
            spyOn($rootScope, '$emit');
            spyOn($ctrl.leaveRequest, 'update').and.callThrough();
            //change to approved
            $ctrl.uiOptions.selectedStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '1'),
              $ctrl.onStatusChanged();
            //entitlements are randomly generated so resetting them to positive here
            if ($ctrl.balance.closing < 0) {
              $ctrl.balance.closing = 0;
            }

            $ctrl.submit();
            $scope.$apply();
          });

          it('calls expected api', function () {
            expect($ctrl.leaveRequest.update).toHaveBeenCalled();
          });

          it('changes status', function () {
            expect($ctrl.leaveRequest.status_id).toEqual(optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1'));
          });

          it('will send update event', function () {
            expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::updatedByManager', $ctrl.leaveRequest);
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
    });
  });
})(CRM);
