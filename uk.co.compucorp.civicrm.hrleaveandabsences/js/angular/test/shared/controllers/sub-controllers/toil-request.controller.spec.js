/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'mocks/data/option-group-mock-data',
    'mocks/data/absence-type-data',
    'mocks/data/leave-request-data',
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
    'leave-absences/shared/controllers/sub-controllers/toil-request.controller',
    'leave-absences/shared/modules/shared-settings'
  ], function (_, optionGroupMock, absenceMockData, mockData) {
    'use strict';

    describe('ToilRequestCtrl', function () {
      var $log, $q, $rootScope, $ctrl, modalInstanceSpy, $scope, $controller, sharedSettings,
        $provide, Contact, ContactAPIMock, AbsenceTypeAPI, AbsenceType, TOILRequestInstance;
      var date2016 = '01/12/2016';
      var role = 'staff'; // change this value to set other roles

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks', 'common.dialog', 'leave-absences.settings',
        function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(['HR_settingsMock', function (HRSettingsMock) {
        $provide.value('HR_settings', HRSettingsMock);
      }]));

      beforeEach(inject(function (_AbsencePeriodAPIMock_,
        _AbsenceTypeAPIMock_, _EntitlementAPIMock_, _WorkPatternAPIMock_,
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_,
        _FileUploaderMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
        $provide.value('FileUploader', _FileUploaderMock_);
      }));

      beforeEach(inject(['api.contact.mock', 'shared-settings', '$q', function (_ContactAPIMock_, _sharedSettings_, _$q_) {
        $provide.value('api.contact', _ContactAPIMock_);
        ContactAPIMock = _ContactAPIMock_;
        sharedSettings = _sharedSettings_;
        $q = _$q_;

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

      beforeEach(inject(function (_$log_, _$controller_, _$rootScope_, _Contact_,
        _AbsenceTypeAPI_, _AbsenceType_, _TOILRequestInstance_, $q) {
        $log = _$log_;
        $rootScope = _$rootScope_;
        $controller = _$controller_;
        AbsenceTypeAPI = _AbsenceTypeAPI_;
        AbsenceType = _AbsenceType_;
        TOILRequestInstance = _TOILRequestInstance_;
        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);
        Contact = _Contact_;

        spyOn($log, 'debug');
        spyOn(AbsenceTypeAPI, 'all').and.callThrough();
        spyOn(AbsenceTypeAPI, 'calculateToilExpiryDate').and.callThrough();
        spyOn(TOILRequestInstance, 'init').and.callThrough();
        spyOn(AbsenceType, 'canExpire').and.callThrough();
        spyOn(Contact, 'all').and.callFake(function () {
          return $q.resolve(ContactAPIMock.mockedContacts());
        });
      }));

      describe('toil request', function () {
        var parentRequestCtrl;

        beforeEach(function () {
          initTestController({ isSelfRecord: true });

          parentRequestCtrl = $controller('RequestCtrl');
        });

        describe('init', function () {
          it('is called', function () {
            expect($log.debug).toHaveBeenCalled();
          });

          it('inherited from request controller', function () {
            expect($ctrl instanceof parentRequestCtrl.constructor).toBe(true);
          });

          it('has leave type set to toil', function () {
            expect($ctrl.isLeaveType('toil')).toBeTruthy();
          });

          it('calls init on toil request instance', function () {
            expect(TOILRequestInstance.init).toHaveBeenCalledWith({
              contact_id: CRM.vars.leaveAndAbsences.contactId
            });
          });

          it('loads toil amounts', function () {
            expect(Object.keys($ctrl.toilAmounts).length).toBeGreaterThan(0);
          });

          it('gets absence types with true allow_accruals_request param', function () {
            expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({
              allow_accruals_request: true
            });
          });

          it('cannot submit request', function () {
            expect($ctrl.canSubmit()).toBe(false);
          });
        });

        describe('create', function () {
          describe('with selected duration and dates', function () {
            beforeEach(function () {
              var toilAccrue = optionGroupMock.specificObject('hrleaveandabsences_toil_amounts', 'name', 'quarter_day');

              setTestDates(date2016, date2016);
              $ctrl.request.toilDurationHours = 1;
              $ctrl.request.updateDuration();
              $ctrl.request.toil_to_accrue = toilAccrue.value;
            });

            it('can submit request', function () {
              expect($ctrl.canSubmit()).toBe(true);
            });

            it('sets expiry date', function () {
              expect($ctrl.expiryDate).toEqual(absenceMockData.calculateToilExpiryDate().values.toil_expiry_date);
            });

            it('calls calculateToilExpiryDate on AbsenceType', function () {
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[0]).toEqual($ctrl.request.type_id);
              expect(AbsenceTypeAPI.calculateToilExpiryDate.calls.mostRecent().args[1]).toEqual($ctrl.request.from_date);
            });

            describe('when user changes number of days selected', function () {
              beforeEach(function () {
                $ctrl.changeInNoOfDays();
              });

              it('does not reset toil attributes', function () {
                expect($ctrl.request.toilDurationHours).not.toEqual('0');
                expect($ctrl.request.toilDurationMinutes).toEqual('0');
                expect($ctrl.request.toil_to_accrue).not.toEqual('');
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
                $rootScope.$broadcast('uploadFiles: success');
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
                expect($ctrl.request.toil_duration).toBeDefined();
                expect($ctrl.request.toil_to_accrue).toBeDefined();
                expect($ctrl.request.toil_expiry_date).toBeDefined();
              });

              it('is successful', function () {
                expect($ctrl.errors.length).toBe(0);
                expect($ctrl.request.id).toBeDefined();
              });

              it('allows user to submit', function () {
                expect($ctrl.canSubmit()).toBeTruthy();
              });

              it('sends event', function () {
                expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::new', $ctrl.request);
              });

              describe('and absence type allows overuse', function () {
                beforeEach(function () {
                  $ctrl.updateBalance();
                  $ctrl.submit();
                  $scope.$digest();
                });

                it('saves without errors', function () {
                  expect($ctrl.errors.length).toBe(0);
                });
              });
            });
          });
        });

        describe('edit', function () {
          var toilRequest, absenceType;

          beforeEach(function () {
            toilRequest = TOILRequestInstance.init(mockData.findBy('request_type', 'toil'));
            toilRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

            initTestController({ leaveRequest: toilRequest });

            absenceType = _.find($ctrl.absenceTypes, function (absenceType) {
              return absenceType.id === $ctrl.request.type_id;
            });
          });

          it('sets balance', function () {
            expect($ctrl.balance.opening).not.toBeLessThan(0);
          });

          it('sets absence types', function () {
            expect(absenceType.id).toEqual(toilRequest.type_id);
          });

          it('does show balance', function () {
            expect($ctrl.uiOptions.showBalance).toBeTruthy();
          });
        });

        describe('respond', function () {
          describe('by manager', function () {
            var expiryDate, originalToilToAccrue, toilRequest;

            beforeEach(function () {
              var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');

              expiryDate = '2017-12-31';
              toilRequest = TOILRequestInstance.init(mockData.findBy('status_id', status));
              toilRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
              toilRequest.toil_expiry_date = expiryDate;
              role = 'manager';

              initTestController({ leaveRequest: toilRequest });
              $ctrl.calculateToilExpiryDate();

              expiryDate = $ctrl._convertDateFormatFromServer($ctrl.request.toil_expiry_date);
              originalToilToAccrue = optionGroupMock.specificObject('hrleaveandabsences_toil_amounts', 'name', 'quarter_day');
              $ctrl.request.toil_to_accrue = originalToilToAccrue.value;
            });

            it('sets role to manager', function () {
              expect($ctrl.isRole('manager')).toBeTruthy();
            });

            it('expiry date is set on ui', function () {
              expect($ctrl.uiOptions.expiryDate).toEqual(expiryDate);
            });

            describe('and changes expiry date', function () {
              var oldExpiryDate, newExpiryDate;

              beforeEach(function () {
                oldExpiryDate = $ctrl.request.toil_expiry_date;
                $ctrl.uiOptions.expiryDate = new Date();
                newExpiryDate = $ctrl._convertDateToServerFormat($ctrl.uiOptions.expiryDate);
                $ctrl.updateExpiryDate();
              });

              it('new expiry date is not same as old expiry date', function () {
                expect(oldExpiryDate).not.toEqual($ctrl.request.toil_expiry_date);
              });

              it('sets new expiry date', function () {
                expect($ctrl.request.toil_expiry_date).toEqual(newExpiryDate);
              });

              describe('and staff edits new request', function () {
                beforeEach(function () {
                  role = 'staff';
                  delete $ctrl.request.id;
                  oldExpiryDate = $ctrl.request.toil_expiry_date;

                  initTestController({ leaveRequest: $ctrl.request });
                  $ctrl.calculateToilExpiryDate();
                });

                it('has expired date updated by staff', function () {
                  expect($ctrl.request.toil_expiry_date).not.toEqual(oldExpiryDate);
                });

                it('has toil amount set by staff', function () {
                  expect($ctrl.request.toil_to_accrue).toEqual(originalToilToAccrue.value);
                });
              });

              describe('and staff edits open request', function () {
                beforeEach(function () {
                  role = 'staff';

                  initTestController({ leaveRequest: $ctrl.request });

                  $ctrl.uiOptions.expiryDate = oldExpiryDate;

                  $ctrl.updateExpiryDate();
                });

                it('has expired date set by manager', function () {
                  expect($ctrl.request.toil_expiry_date).toEqual(oldExpiryDate);
                });

                it('has toil amount set by manager', function () {
                  expect($ctrl.request.toil_to_accrue).toEqual(originalToilToAccrue.value);
                });
              });
            });
          });
        });

        describe('when TOIL Request does not expire', function () {
          beforeEach(function () {
            AbsenceType.canExpire.and.returnValue($q.resolve(false));
            initTestController({ leaveRequest: $ctrl.request });
          });

          it('should set requestCanExpire to false', function () {
            expect($ctrl.requestCanExpire).toBe(false);
          });

          describe('when request date changes', function () {
            beforeEach(function () {
              spyOn(AbsenceType, 'calculateToilExpiryDate');
              $ctrl.request.to_date = new Date();
              $ctrl.calculateToilExpiryDate();
              $rootScope.$digest();
            });

            it('should not calculate the expiry date field', function () {
              expect(AbsenceType.calculateToilExpiryDate).not.toHaveBeenCalled();
            });

            it('should set expiry date to false', function () {
              expect($ctrl.request.toil_expiry_date).toBe(false);
            });
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

        $ctrl = $controller('ToilRequestCtrl', {
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
