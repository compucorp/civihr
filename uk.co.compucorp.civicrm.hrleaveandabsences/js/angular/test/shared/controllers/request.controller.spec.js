/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/lodash',
    'common/moment',
    'leave-absences/mocks/data/option-group.data',
    'leave-absences/mocks/data/leave-request.data',
    'leave-absences/manager-leave/app',
    'common/mocks/services/hr-settings-mock',
    'leave-absences/mocks/apis/absence-period-api-mock',
    'leave-absences/mocks/apis/absence-type-api-mock',
    'leave-absences/mocks/apis/entitlement-api-mock',
    'leave-absences/mocks/apis/leave-request-api-mock',
    'leave-absences/mocks/apis/option-group-api-mock',
    'leave-absences/mocks/apis/work-pattern-api-mock',
    'leave-absences/shared/services/leave-request.service',
    'leave-absences/manager-leave/app'
  ], function (_, moment, optionGroupMock, mockData) {
    'use strict';

    describe('LeaveRequestCtrl', function () {
      var $log, $rootScope, controller, modalInstanceSpy, $scope, $q, dialog, $controller,
        $provide, sharedSettings, AbsenceTypeAPI, AbsencePeriodAPI, LeaveRequestInstance,
        Contact, ContactAPIMock, EntitlementAPI, LeaveRequestAPI, pubSub,
        requiredTab, WorkPatternAPI, LeaveRequestService;
      var role = 'staff'; // change this value to set other roles

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks', 'common.dialog', 'common.services',
        'leave-absences.settings', 'manager-leave',
        function (_$provide_, $exceptionHandlerProvider) {
          $provide = _$provide_;
          // this will consume all throw
          $exceptionHandlerProvider.mode('log');
        }));

      beforeEach(inject(function (
        _AbsencePeriodAPIMock_, _AbsenceTypeAPIMock_, _EntitlementAPIMock_, _WorkPatternAPIMock_,
        _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
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
        _LeaveRequestInstance_, _LeaveRequest_, _LeaveRequestAPI_, _pubSub_,
        _WorkPatternAPI_, _LeaveRequestService_) {
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
        pubSub = _pubSub_;

        LeaveRequestInstance = _LeaveRequestInstance_;
        LeaveRequestService = _LeaveRequestService_;

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
        spyOn(pubSub, 'subscribe').and.callThrough();
        spyOn(pubSub, 'publish').and.callThrough();
        spyOn(WorkPatternAPI, 'getCalendar').and.callThrough();
        spyOn(EntitlementAPI, 'all').and.callThrough();

        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);
        requiredTab = {
          isRequired: true,
          canSubmit: function () {
            return true;
          },
          onBeforeSubmit: jasmine.createSpy('onBeforeSubmit').and.returnValue($q.resolve())
        };
      }));

      describe('staff opens request popup', function () {
        var leaveRequest;

        beforeEach(inject(function () {
          leaveRequest = LeaveRequestInstance.init();
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          initTestController({ leaveRequest: leaveRequest });
        }));

        it('is called', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('getStatuses returns an array', function () {
          expect(controller.getStatuses()).toEqual(jasmine.any(Array));
        });

        describe('when initialized', function () {
          describe('before date is selected', function () {
            beforeEach(function () {
              $scope.$digest();
            });

            it('has leave type set to leave', function () {
              expect(controller.isLeaveType('leave')).toBeTruthy();
            });

            it('has absence period is set', function () {
              expect(controller.period).toEqual(jasmine.any(Object));
            });

            it('has current period selected', function () {
              expect(controller.period.current).toBeTruthy();
            });

            it('has absence types loaded', function () {
              expect(controller.absenceTypes).toBeDefined();
              expect(controller.absenceTypes.length).toBeGreaterThan(0);
            });

            it('loads calculation units into absence types', function () {
              expect(controller.absenceTypes[0].calculation_unit_name).toEqual(jasmine.any(String));
            });

            it('has first absence type selected', function () {
              expect(controller.request.type_id).toEqual(controller.absenceTypes[0].id);
            });

            it('has nil balance change amount', function () {
              expect(controller.balance.change.amount).toEqual(0);
            });

            it('gets absence types with false sick param', function () {
              expect(AbsenceTypeAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
                is_sick: false
              }));
            });

            it('allows to change absence type', function () {
              expect(controller.canChangeAbsenceType()).toBeTruthy();
            });

            describe('leave request instance', function () {
              it('has new instance created', function () {
                expect(controller.request).toEqual(jasmine.any(Object));
              });

              it('has contact_id set', function () {
                expect(controller.request.contact_id).toBeDefined();
              });

              it('does not have from/to dates set', function () {
                expect(controller.request.from_date).toBeUndefined();
                expect(controller.request.to_date).toBeUndefined();
              });
            });
          });

          describe('on absence period change', function () {
            beforeEach(function () {
              $rootScope.$broadcast('LeaveRequestPopup::absencePeriodChanged');
            });

            it('starts reloading entitlements', function () {
              expect(controller.loading.entitlements).toBeTruthy();
            });

            it('does not allow to change absence type before entitlemenets are updated', function () {
              expect(controller.canChangeAbsenceType()).toBeFalsy();
            });

            describe('once it started reloading entitlements', function () {
              beforeEach(function () {
                spyOn($rootScope, '$emit').and.callThrough();
                $rootScope.$digest();
              });

              it('loads entitlements for the selected period', function () {
                expect(EntitlementAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
                  period_id: controller.period.id
                }), true);
              });

              it('finishes loading entitlements', function () {
                expect(controller.loading.entitlements).toBeFalsy();
              });

              it('broadcasts absence types with updated entitlements back', function () {
                expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequestPopup::absencePeriodBalancesUpdated', controller.absenceTypes);
              });

              it('allows to change absence type again', function () {
                expect(controller.canChangeAbsenceType()).toBeTruthy();
              });
            });
          });
        });

        describe('when user cancels dialog (clicks X), or back button', function () {
          beforeEach(function () {
            controller.dismissModal();
          });

          it('closes model', function () {
            expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
          });
        });

        describe('save leave request', function () {
          describe('does not allow multiple save', function () {
            beforeEach(function () {
              controller.submit();
            });

            it('user cannot submit again', function () {
              expect(controller.submitting).toBeTruthy();
            });

            it('does not create request again', function () {
              spyOn(controller.request, 'create').and.callThrough();
              controller.submit();
              expect(controller.request.create).not.toHaveBeenCalled();
            });
          });

          describe('when submit with invalid fields', function () {
            beforeEach(function () {
              requiredTab.canSubmit = function () { return false; };
              $scope.$emit('LeaveRequestPopup::addTab', requiredTab);
              controller.submit();
              $scope.$digest();
            });

            it('fails with error', function () {
              expect(controller.errors).toEqual(jasmine.any(Array));
            });

            it('does not allow user to submit', function () {
              expect(controller.canSubmit()).toBeFalsy();
            });
          });

          describe('when submit with valid fields', function () {
            beforeEach(function () {
              LeaveRequestAPI.isValid.and.returnValue($q.resolve());
              LeaveRequestAPI.create.and.returnValue($q.resolve({ id: '1' }));
              controller.balance.closing = 1;
              controller.request.from_date_amount = 1;
              controller.request.to_date_amount = 1;
              controller.request.from_date_type = 1;
              controller.request.to_date_type = 1;
            });

            describe('basic tests', function () {
              var hasCalledRequestCreateMethod, nonRequiredTab;

              beforeEach(function () {
                nonRequiredTab = {
                  onBeforeSubmit: jasmine.createSpy('onBeforeSubmit').and.returnValue($q.resolve())
                };
                controller.request.change_balance = true;

                requiredTab.onBeforeSubmit.and.callFake(function () {
                  hasCalledRequestCreateMethod = LeaveRequestAPI.create.calls.count() > 0;

                  return $q.resolve();
                });
                $scope.$emit('LeaveRequestPopup::addTab', requiredTab);
                $scope.$emit('LeaveRequestPopup::addTab', nonRequiredTab);
                controller.submit();
                $scope.$digest();
              });

              it('is successful', function () {
                expect(controller.errors.length).toBe(0);
                expect(controller.request.id).toBeDefined();
              });

              it('submits all tabs', function () {
                expect(requiredTab.onBeforeSubmit).toHaveBeenCalled();
                expect(nonRequiredTab.onBeforeSubmit).toHaveBeenCalled();
              });

              it('submits all tabs before saving the request', function () {
                expect(hasCalledRequestCreateMethod).toBe(false);
              });

              it('calls corresponding API end points', function () {
                expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
                expect(LeaveRequestAPI.create).toHaveBeenCalled();
              });

              it('sends event', function () {
                expect(pubSub.publish).toHaveBeenCalledWith('LeaveRequest::new', controller.request);
              });

              it('does not send leave in hours parameters to the server', function () {
                expect(controller.request['from_date_amount']).not.toBeDefined();
                expect(controller.request['to_date_amount']).not.toBeDefined();
              });

              describe('when one of the tabs fails to submit', function () {
                beforeEach(function () {
                  nonRequiredTab.onBeforeSubmit.and.returnValue($q.reject());
                  LeaveRequestAPI.create.calls.reset();
                  controller.submit();
                  $scope.$digest();
                });

                it('does not save the leave request', function () {
                  expect(LeaveRequestAPI.create).not.toHaveBeenCalled();
                });
              });
            });

            describe('when calculation unit is "hours"', function () {
              beforeEach(function () {
                controller.selectedAbsenceType.calculation_unit_name = 'hours';

                controller.submit();
                $scope.$digest();
              });

              it('does not send deduction in hours parameters to the server', function () {
                expect(controller.request['from_date_type']).not.toBeDefined();
                expect(controller.request['from_date_type']).not.toBeDefined();
              });
            });
          });
        });

        describe('canSubmit()', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

            initTestController({
              mode: 'edit',
              leaveRequest: leaveRequest
            });
          });

          it('does not allow to submit the leave request without changes', function () {
            expect(controller.canSubmit()).toBe(false);
          });

          describe('when a comment is added', function () {
            beforeEach(function () {
              controller.request.comments.push(jasmine.any(Object));
            });

            it('allows to submit the leave request', function () {
              expect(controller.canSubmit()).toBe(true);
            });
          });

          describe('tabs', function () {
            describe('on create', function () {
              beforeEach(function () {
                controller.mode = 'create';
              });

              describe('when all required tabs can be submitted', function () {
                beforeEach(function () {
                  $scope.$emit('LeaveRequestPopup::addTab', requiredTab);
                  $scope.$emit('LeaveRequestPopup::addTab', requiredTab);
                  $scope.$emit('LeaveRequestPopup::addTab', { canSubmit: returnFalse });
                });

                it('allows to submit the leave request', function () {
                  expect(controller.canSubmit()).toBe(true);
                });
              });

              describe('when not all required tabs can be submitted', function () {
                beforeEach(function () {
                  $scope.$emit('LeaveRequestPopup::addTab', { isRequired: true, canSubmit: returnFalse });
                  $scope.$emit('LeaveRequestPopup::addTab', { isRequired: true, canSubmit: returnTrue });
                  $scope.$emit('LeaveRequestPopup::addTab', { canSubmit: returnTrue });
                });

                it('does not allow to submit the leave request', function () {
                  expect(controller.canSubmit()).toBe(false);
                });
              });
            });

            describe('on edit', function () {
              beforeEach(function () {
                controller.mode = 'edit';
              });

              describe('when some of the non-required tabs can be submitted', function () {
                beforeEach(function () {
                  $scope.$emit('LeaveRequestPopup::addTab', requiredTab);
                  $scope.$emit('LeaveRequestPopup::addTab', { canSubmit: returnTrue });
                  $scope.$emit('LeaveRequestPopup::addTab', { canSubmit: returnFalse });
                });

                it('allows to submit the leave request', function () {
                  expect(controller.canSubmit()).toBe(true);
                });
              });

              describe('when none of the non-required tabs can be submitted', function () {
                beforeEach(function () {
                  $scope.$emit('LeaveRequestPopup::addTab', requiredTab);
                  $scope.$emit('LeaveRequestPopup::addTab', { canSubmit: returnFalse });
                  $scope.$emit('LeaveRequestPopup::addTab', { canSubmit: returnFalse });
                });

                it('does not allow to submit the leave request', function () {
                  expect(controller.canSubmit()).toBe(false);
                });
              });
            });

            function returnTrue () {
              return true;
            }

            function returnFalse () {
              return false;
            }
          });
        });

        describe('in view mode', function () {
          var leaveRequest;

          beforeEach(function () {
            var approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');

            leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', approvalStatus));
            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

            initTestController({ leaveRequest: leaveRequest });
          });

          it('sets mode to view', function () {
            expect(controller.isMode('view')).toBeTruthy();
          });

          it('sets contact id', function () {
            expect(controller.request.contact_id).toEqual(leaveRequest.contact_id);
          });

          it('does not allow to change absence type', function () {
            expect(controller.canChangeAbsenceType()).toBeFalsy();
          });

          describe('on submit', function () {
            beforeEach(function () {
              spyOn(controller.request, 'update').and.callThrough();
              controller.submit();
              $scope.$apply();
            });

            it('does not update leave request', function () {
              expect(controller.request.update).not.toHaveBeenCalled();
            });
          });
        });

        describe('when user edits leave request', function () {
          describe('without comments', function () {
            var leaveRequest;

            beforeEach(function () {
              var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
              leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

              leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

              spyOn(controller.request, 'update').and.callThrough();
              initTestController({ leaveRequest: leaveRequest });
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
                expect(controller.request.from_date).toEqual(leaveRequest.from_date);
                expect(controller.request.from_date_type).toEqual(leaveRequest.from_date_type);
                expect(controller.request.to_date).toEqual(leaveRequest.to_date);
                expect(controller.request.to_date_type).toEqual(leaveRequest.to_date_type);
              });

              it('does not allow user to submit', function () {
                expect(controller.canSubmit()).toBeFalsy();
              });
            });

            describe('and submits', function () {
              beforeEach(function () {
                spyOn(controller.request, 'update').and.callThrough();

                // entitlements are randomly generated so resetting them to positive here
                if (controller.balance.closing < 0) {
                  controller.balance.closing = 5;
                }

                controller.submit();
                $scope.$apply();
              });

              it('does not force balance change to be recalculated', function () {
                expect(controller.request.change_balance).not.toBeDefined();
              });

              it('calls appropriate API endpoint', function () {
                expect(controller.request.update).toHaveBeenCalled();
              });

              it('sends edit event', function () {
                expect(pubSub.publish).toHaveBeenCalledWith('LeaveRequest::edit', controller.request);
              });

              it('has no error', function () {
                expect(controller.errors.length).toBe(0);
              });

              it('closes model popup', function () {
                expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
              });
            });

            describe('manager asks for more information', function () {
              var expectedStatusValue;

              beforeEach(function () {
                var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '4');
                var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

                leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();

                initTestController({ leaveRequest: leaveRequest, isSelfRecord: true });

                expectedStatusValue = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
                controller.balance.closing = 5;

                controller.submit();
              });

              it('status changes to waiting approval before calling API', function () {
                expect(controller.request.status_id).toEqual(expectedStatusValue);
              });
            });
          });
        });
      });

      describe('manager opens leave request popup in edit mode', function () {
        describe('when leave request date has same date as period end date', function () {
          beforeEach(function () {
            var approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

            leaveRequest.from_date = '2017-12-31 00:00:00';
            leaveRequest.to_date = '2017-12-31 23:59:00';
            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            role = 'manager';

            initTestController({ leaveRequest: leaveRequest });
            spyOn(controller.request, 'update').and.callThrough();
            spyOn(LeaveRequestInstance, 'calculateBalanceChange').and.returnValue(
              $q.resolve({ amount: controller.balance.change.amount }));
            leaveRequest.status_id = approvalStatus;
            controller.submit();
            $rootScope.$digest();
          });

          it('matches leave request with according absence period', function () {
            expect(controller.period).toBeDefined();
          });
        });

        describe('basic tests for when manager opens leave request popup in edit mode', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            role = 'manager';

            initTestController({ leaveRequest: leaveRequest });
          });

          describe('on initialization', function () {
            var waitingApprovalStatus;

            beforeEach(function () {
              waitingApprovalStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'value', '3');
            });

            it('sets the manager role', function () {
              expect(controller.isRole('manager')).toBeTruthy();
            });

            it('sets all leaverequest values', function () {
              expect(controller.request.contact_id).toEqual('' + CRM.vars.leaveAndAbsences.contactId);
              expect(controller.request.type_id).toEqual(jasmine.any(String));
              expect(controller.request.status_id).toEqual(waitingApprovalStatus.value);
              expect(controller.request.from_date).toEqual(jasmine.any(String));
              expect(controller.request.from_date_type).toEqual(jasmine.any(String));
              expect(controller.request.to_date).toEqual(jasmine.any(String));
              expect(controller.request.to_date_type).toEqual(jasmine.any(String));
            });

            it('gets contact name', function () {
              expect(controller.contactName).toEqual(jasmine.any(String));
            });

            it('does not allow user to submit', function () {
              expect(controller.canSubmit()).toBeFalsy();
            });

            it('does not allow to change absence type', function () {
              expect(controller.canChangeAbsenceType()).toBeFalsy();
            });
          });

          describe('on submit', function () {
            var calculatedBalanceChangeAmount;

            beforeEach(function () {
              controller.balance.change.amount = controller.request.balance_change;
              calculatedBalanceChangeAmount = controller.balance.change.amount;

              spyOn($rootScope, '$emit');
              spyOn(controller.request, 'update').and.callThrough();
              // Pretending original balance change has not been updated
              spyOn(LeaveRequestInstance, 'calculateBalanceChange').and.returnValue(
                $q.resolve({ amount: calculatedBalanceChangeAmount }));

              // entitlements are randomly generated so resetting them to positive here
              if (controller.balance.closing < 0) {
                controller.balance.closing = 0;
              }
              // set status id manually as manager would set it on UI
              controller.newStatusOnSave = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
            });

            describe('if balance change has not been updated', function () {
              beforeEach(function () {
                spyOn(controller.request,
                  'checkIfBalanceChangeNeedsRecalculation')
                  .and.returnValue($q.resolve(false));
                controller.submit();
                $scope.$digest();
              });

              it('allows user to submit', function () {
                expect(controller.canSubmit()).toBeTruthy();
              });

              it('forces balance change to be recalculated', function () {
                expect(controller.request.change_balance).toBeTruthy();
              });

              it('calls update method on instance', function () {
                expect(controller.request.update).toHaveBeenCalled();
              });

              it('calls corresponding API end points', function () {
                expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
                expect(LeaveRequestAPI.update).toHaveBeenCalled();
              });

              it('sends update event', function () {
                expect(pubSub.publish).toHaveBeenCalledWith('LeaveRequest::updatedByManager', controller.request);
              });
            });

            describe('if balance change has been updated', function () {
              var proceedWithBalanceChangeRecalculation;

              beforeEach(function () {
                // Make original balance differ from the calculated balance
                controller.balance.change.amount--;

                spyOn(controller.request,
                  'checkIfBalanceChangeNeedsRecalculation')
                  .and.returnValue($q.resolve(true));
                spyOn(LeaveRequestService,
                  'promptBalanceChangeRecalculation')
                  .and.callThrough();
                spyOn(dialog, 'open').and.callFake(function (params) {
                  proceedWithBalanceChangeRecalculation = params.onConfirm;
                });
              });

              describe('basic tests', function () {
                beforeEach(function () {
                  controller.submit();
                  $rootScope.$digest();
                });

                it('does not call update method on instance', function () {
                  expect(controller.request.update).not.toHaveBeenCalled();
                });

                it('prompts a balance change recalculation', function () {
                  expect(LeaveRequestService.promptBalanceChangeRecalculation)
                    .toHaveBeenCalled();
                });

                describe('when confirming balance change recalculation', function () {
                  beforeEach(function () {
                    proceedWithBalanceChangeRecalculation();
                    $rootScope.$digest();
                  });

                  it('initiates the balance change recalculation', function () {
                    expect($rootScope.$emit).toHaveBeenCalledWith(
                      'LeaveRequestPopup::recalculateBalanceChange');
                  });

                  describe('after recalculation on submit attempt', function () {
                    beforeEach(function () {
                      controller.balance.change.amount = calculatedBalanceChangeAmount;
                      controller.submit();
                      $rootScope.$digest();
                    });

                    it('updates leave request', function () {
                      expect(controller.request.update).toHaveBeenCalled();
                    });
                  });
                });
              });

              describe('when cancelling request', function () {
                var requestOriginalDates = {};

                beforeEach(function () {
                  requestOriginalDates.from = controller.request.from_date;
                  requestOriginalDates.to = controller.request.to_date;
                  controller.newStatusOnSave = optionGroupMock.specificObject(
                    'hrleaveandabsences_leave_request_status', 'name', 'cancelled').value;

                  controller.submit();
                  $rootScope.$digest();
                });

                it('does not check the balance change', function () {
                  expect(LeaveRequestService.promptBalanceChangeRecalculation)
                    .not.toHaveBeenCalled();
                });

                it('updates request', function () {
                  expect(controller.request.update).toHaveBeenCalled();
                });

                it('tells the backend to not recalculate balance change', function () {
                  expect(controller.request.change_balance).toBeUndefined();
                });

                it('reverts original request times', function () {
                  expect(moment(controller.request.from_date).format('HH:mm')).toEqual(
                    moment(requestOriginalDates.from).format('HH:mm'));
                  expect(moment(controller.request.to_date).format('HH:mm')).toEqual(
                    moment(requestOriginalDates.to).format('HH:mm'));
                });
              });

              describe('when rejecting request', function () {
                var requestOriginalDates = {};

                beforeEach(function () {
                  requestOriginalDates.from = controller.request.from_date;
                  requestOriginalDates.to = controller.request.to_date;
                  controller.newStatusOnSave = optionGroupMock.specificObject(
                    'hrleaveandabsences_leave_request_status', 'name', 'rejected').value;
                  // testing if time has been adjusted in UI, for example, due to work pattern change
                  controller.request.from_date = controller.request.from_date.split(' ')[0] + ' 01:01';
                  controller.request.to_date = controller.request.to_date.split(' ')[0] + ' 01:02';

                  controller.submit();
                  $rootScope.$digest();
                });

                it('does not check the balance change', function () {
                  expect(LeaveRequestService.promptBalanceChangeRecalculation)
                    .not.toHaveBeenCalled();
                });

                it('updates request', function () {
                  expect(controller.request.update).toHaveBeenCalled();
                });

                it('tells the backend to not recalculate balance change', function () {
                  expect(controller.request.change_balance).toBeUndefined();
                });

                it('reverts original request times', function () {
                  expect(moment(controller.request.from_date).format('HH:mm')).toEqual(
                    moment(requestOriginalDates.from).format('HH:mm'));
                  expect(moment(controller.request.to_date).format('HH:mm')).toEqual(
                    moment(requestOriginalDates.to).format('HH:mm'));
                });
              });
            });

            describe('in case leave request is TOIL', function () {
              beforeEach(function () {
                controller.request.request_type = 'toil';
                controller.balance.change.amount--;

                controller.submit();
                spyOn(dialog, 'open');
                $rootScope.$apply();
              });

              it('ignores balance changes and does not open confirmation dialog', function () {
                expect(dialog.open).not.toHaveBeenCalled();
              });

              it('updates the request straight away', function () {
                expect(controller.request.update).toHaveBeenCalled();
              });
            });
          });

          describe('when the popup is closed', function () {
            beforeEach(function () {
              controller.closeAlert();
            });

            it('flushes any current errors', function () {
              expect(controller.errors).toEqual([]);
            });
          });
        });
      });

      describe('manager raises absence request on behalf of staff', function () {
        var leaveRequest;

        beforeEach(function () {
          role = 'manager';
          leaveRequest = LeaveRequestInstance.init();

          initTestController({ leaveRequest: leaveRequest });
        });

        it('does not set contact', function () {
          expect(controller.contactName).toBeNull();
        });

        it('does not initialize absence types', function () {
          expect(AbsenceTypeAPI.all).not.toHaveBeenCalled();
        });

        describe('before contact is selected', function () {
          it('sets post contact selection as false', function () {
            expect(controller.postContactSelection).toBe(false);
          });

          it('sets staff member selection as false', function () {
            expect(controller.staffMemberSelectionComplete).toBe(false);
          });
        });

        describe('after contact is selected', function () {
          describe('when loading entitlements for the staff', function () {
            beforeEach(function () {
              controller.initAfterContactSelection();
            });

            it('sets post contact selection as true', function () {
              expect(controller.postContactSelection).toBe(true);
            });
          });

          describe('when entitlement is present', function () {
            var approvalStatus;

            beforeEach(function () {
              approvalStatus = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
              controller.request.contact_id = '204';
              controller.initAfterContactSelection();
              $scope.$digest();
            });

            it('sets manager role', function () {
              expect(controller.isRole('manager')).toBeTruthy();
            });

            it('sets create mode', function () {
              expect(controller.isMode('create')).toBeTruthy();
            });

            it('does not initialize absence types', function () {
              expect(AbsenceTypeAPI.all).toHaveBeenCalled();
            });

            it('sets status to approved', function () {
              expect(controller.newStatusOnSave).toEqual(approvalStatus);
            });

            it('sets post contact selection back to false', function () {
              expect(controller.postContactSelection).toBe(false);
            });

            it('sets staff member selection as true', function () {
              expect(controller.staffMemberSelectionComplete).toBe(true);
            });

            describe('cancelled status', function () {
              var cancelStatus, availableStatuses;

              beforeEach(function () {
                cancelStatus = optionGroupMock.specificObject('hrleaveandabsences_leave_request_status', 'name', 'cancelled');
                availableStatuses = controller.getStatuses();
              });

              it('is not available', function () {
                expect(availableStatuses).not.toContain(cancelStatus);
              });
            });

            describe('and then select a staff without entitlements', function () {
              beforeEach(function () {
                EntitlementAPI.all.and.returnValue($q.resolve([]));
                controller.initAfterContactSelection();
                $scope.$digest();
              });

              it('sets staff member selection complete as false', function () {
                expect(controller.staffMemberSelectionComplete).toBe(false);
              });
            });
          });

          describe('when no entitlements are present', function () {
            beforeEach(function () {
              EntitlementAPI.all.and.returnValue($q.resolve([]));
              controller.initAfterContactSelection();
              $scope.$digest();
            });

            it('sets staff member selection complete as false', function () {
              expect(controller.staffMemberSelectionComplete).toBe(false);
            });
          });
        });

        describe('after contact is deselected', function () {
          var promise;

          beforeEach(function () {
            controller.request.contact_id = undefined;
            promise = controller.initAfterContactSelection();
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
        var adminId = '206';

        beforeEach(function () {
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

          leaveRequest.contact_id = adminId.toString();

          role = 'admin';
          initTestController({ leaveRequest: leaveRequest });
        });

        describe('on initialization', function () {
          it('is in edit mode', function () {
            expect(controller.isMode('edit')).toBeTruthy();
          });

          it('has admin role', function () {
            expect(controller.isRole('admin')).toBeTruthy();
          });

          it('does not load contacts', function () {
            expect(controller.managedContacts.length).toEqual(0);
          });

          it('selects the absence period that contains the leave request dates', function () {
            expect(moment(controller.period.start_date).isSameOrBefore(
              moment(controller.request.from_date))).toBeTruthy();
            expect(moment(controller.period.end_date).isSameOrAfter(
              moment(controller.request.to_date))).toBeTruthy();
          });
        });
      });

      describe('admin opens leave request popup in view mode', function () {
        beforeEach(function () {
          var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');
          var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

          role = 'admin';
          initTestController({ leaveRequest: leaveRequest });
        });

        describe('on initialization', function () {
          it('allows to change absence type', function () {
            expect(controller.canChangeAbsenceType()).toBeTruthy();
          });
        });
      });

      describe('admin opens leave request popup in create mode', function () {
        var leaveRequest;
        var adminId = '206';

        beforeEach(function () {
          leaveRequest = LeaveRequestInstance.init();
          leaveRequest.contact_id = adminId.toString();
          role = 'admin';

          initTestController({ leaveRequest: leaveRequest });
        });

        describe('on initialization', function () {
          it('is in create mode', function () {
            expect(controller.isMode('create')).toBeTruthy();
          });

          it('has admin role', function () {
            expect(controller.isRole('admin')).toBeTruthy();
          });

          it('does not contain admin in the list of managees', function () {
            expect(_.find(controller.managedContacts, { 'id': adminId })).toBeUndefined();
          });
        });
      });

      describe('admin opens leave request popup in create mode for a pre-selected contact', function () {
        var leaveRequest;
        var selectedContactId = '208';
        var adminId = '206';

        beforeEach(function () {
          leaveRequest = LeaveRequestInstance.init();
          leaveRequest.contact_id = adminId.toString();

          role = 'admin';

          initTestController({ leaveRequest: leaveRequest, selectedContactId: selectedContactId });
        });

        describe('on initialization', function () {
          it('is in create mode', function () {
            expect(controller.isMode('create')).toBeTruthy();
          });

          it('has admin role', function () {
            expect(controller.isRole('admin')).toBeTruthy();
          });

          it('has pre-selected contact id', function () {
            expect(controller.request.contact_id).toEqual(selectedContactId);
          });

          it('loads exactly one contact', function () {
            expect(controller.managedContacts.length).toEqual(1);
          });
        });
      });

      describe('deleteLeaveRequest()', function () {
        var confirmFunction, leaveRequest;

        beforeEach(function () {
          spyOn(dialog, 'open').and.callFake(function (params) {
            confirmFunction = params.onConfirm;
          });
          leaveRequest = LeaveRequestInstance.init();

          initTestController({ leaveRequest: leaveRequest });
          controller.deleteLeaveRequest();
        });

        it('confirms before deleting the leave request', function () {
          expect(dialog.open).toHaveBeenCalled();
        });

        describe('when deletion is confirmed', function () {
          var promise;

          beforeEach(function () {
            spyOn(controller, 'dismissModal');
            controller.request = jasmine.createSpyObj(['delete']);
            controller.request.delete.and.returnValue($q.resolve([]));
            promise = confirmFunction();
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('deletes the leave request', function () {
            expect(controller.request.delete).toHaveBeenCalled();
          });

          it('closes the leave modal', function () {
            promise.then(function () {
              expect(controller.dismissModal).toHaveBeenCalled();
            });
          });

          it('publishes a delete event', function () {
            promise.then(function () {
              expect(pubSub.publish).toHaveBeenCalledWith('LeaveRequest::deleted', controller.directiveOptions.leaveRequest);
            });
          });
        });
      });

      describe('getStatuses', function () {
        var leaveRequest;
        var status = {};

        beforeEach(function () {
          var collectionName = 'hrleaveandabsences_leave_request_status';
          var allStatuses = optionGroupMock.getCollection(collectionName);

          status = _.indexBy(allStatuses, 'name');
          leaveRequest = LeaveRequestInstance.init();
          leaveRequest.status_id = null;

          initTestController({ leaveRequest: leaveRequest });

          controller.requestStatuses = status;
        });

        describe('when request is not defined', function () {
          beforeEach(function () {
            delete controller.request;
          });

          it('returns an empty array', function () {
            expect(controller.getStatuses()).toEqual([]);
          });
        });

        describe('when requestStatuses is empty', function () {
          beforeEach(function () {
            controller.requestStatuses = {};
          });

          it('returns an empty array', function () {
            expect(controller.getStatuses()).toEqual([]);
          });
        });

        describe('when previous status was not defined', function () {
          it('returns *More Information Required, Approve* ', function () {
            expect(controller.getStatuses()).toEqual([
              status.more_information_required,
              status.approved
            ]);
          });
        });

        describe('when previous status is *Awaiting Approval*', function () {
          beforeEach(function () {
            controller.request.status_id = status.awaiting_approval.value;
          });

          it('returns *More Information Required, Approve, Reject, Cancel*', function () {
            expect(controller.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *More Information Required*', function () {
          beforeEach(function () {
            controller.request.status_id = status.more_information_required.value;
          });

          it('returns *Approve, More Information Required, Reject, Cancel* ', function () {
            expect(controller.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *Rejected*', function () {
          beforeEach(function () {
            controller.request.status_id = status.rejected.value;
          });

          it('returns *Approve, More Information Required, Reject, Cancel*', function () {
            expect(controller.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *Approved*', function () {
          beforeEach(function () {
            controller.request.status_id = status.approved.value;
          });

          it('returns *Approve, More Information Required, Reject, Cancel* ', function () {
            expect(controller.getStatuses()).toEqual([
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });

        describe('when previous status is *Cancelled*', function () {
          beforeEach(function () {
            controller.request.status_id = status.cancelled.value;
          });

          it('returns *Awaiting Approval , Approve, More Information Required, Reject, Cancel* ', function () {
            expect(controller.getStatuses()).toEqual([
              status.awaiting_approval,
              status.more_information_required,
              status.approved,
              status.rejected,
              status.cancelled
            ]);
          });
        });
      });

      describe('user edits their own leave request popup', function () {
        var leaveRequest;

        ['staff', 'manager', 'admin'].forEach(function (permissionsRole) {
          testRoleForSelfRecord(permissionsRole);
        });

        /**
         * Tests the role for the self record and expects it to be "staff"
         *
         * @param {String} permissionsRole (staff|manager|admin)
         */
        function testRoleForSelfRecord (permissionsRole) {
          describe('when user is ' + permissionsRole, function () {
            beforeEach(function () {
              $rootScope.section = 'my-leave';
              role = permissionsRole;
              leaveRequest = LeaveRequestInstance.init();

              initTestController({ leaveRequest: leaveRequest });
            });

            it('sets the staff role', function () {
              expect(controller.isRole('staff')).toBeTruthy();
            });
          });
        }
      });

      /**
       * Initialize the controller
       *
       * @param leave request
       */
      function initTestController (directiveOptions) {
        $scope = $rootScope.$new();
        directiveOptions = directiveOptions || {};

        controller = $controller('RequestCtrl', {
          $scope: $scope,
          $uibModalInstance: modalInstanceSpy,
          directiveOptions: directiveOptions
        });

        $scope.$digest();
      }
    });
  });
})(CRM);
