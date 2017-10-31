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
    'common/mocks/services/api/contact-mock',
    'common/services/pub-sub',
    'leave-absences/shared/modules/shared-settings'
  ], function (_, moment, optionGroupMock, mockData) {
    'use strict';

    describe('LeaveRequestCtrl', function () {
      var $log, $rootScope, controller, modalInstanceSpy, $scope, $q, dialog, $controller,
        $provide, sharedSettings, AbsenceTypeAPI, AbsencePeriodAPI, LeaveRequestInstance,
        Contact, ContactAPIMock, EntitlementAPI, LeaveRequestAPI, pubSub,
        WorkPatternAPI;
      var role = 'staff'; // change this value to set other roles

      beforeEach(module('leave-absences.templates', 'leave-absences.controllers',
        'leave-absences.mocks', 'common.mocks', 'common.dialog', 'common.services',
        'leave-absences.settings',
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
        _WorkPatternAPI_) {
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

            it('has first absence type selected', function () {
              expect(controller.request.type_id).toEqual(controller.absenceTypes[0].id);
            });

            it('has nil balance change amount', function () {
              expect(controller.balance.change.amount).toEqual(0);
            });

            it('gets absence types with false sick param', function () {
              expect(AbsenceTypeAPI.all).toHaveBeenCalledWith({
                is_sick: false
              });
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

            it('submit does not create request again', function () {
              spyOn(controller.request, 'create').and.callThrough();
              controller.submit();
              expect(controller.request.create).not.toHaveBeenCalled();
            });
          });

          describe('when submit with invalid fields', function () {
            beforeEach(function () {
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

              controller.submit();
              $scope.$digest();
            });

            it('is successful', function () {
              expect(controller.errors.length).toBe(0);
              expect(controller.request.id).toBeDefined();
            });

            it('calls corresponding API end points', function () {
              expect(LeaveRequestAPI.isValid).toHaveBeenCalled();
              expect(LeaveRequestAPI.create).toHaveBeenCalled();
            });

            it('sends event', function () {
              expect(pubSub.publish).toHaveBeenCalledWith('LeaveRequest::new', controller.request);
            });
          });
        });

        describe('canSubmit()', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            leaveRequest.fileUploader = { queue: [] };

            initTestController({ leaveRequest: leaveRequest });
          });

          it('does not allow to submit the leave request without changes', function () {
            expect(controller.canSubmit()).toBe(false);
          });

          describe('when a comment is added', function () {
            beforeEach(function () {
              controller.request.comments.push(jasmine.any(Object));
              controller.checkSubmitConditions = jasmine.createSpy('checkSubmitConditions');
              controller.checkSubmitConditions.and.returnValue(true);
            });

            it('allows to submit the leave request', function () {
              expect(controller.canSubmit()).toBe(true);
            });
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
            beforeEach(function () {
              var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
              var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

              leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
              leaveRequest.fileUploader = { queue: [] };

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
                expect(controller.request.from_date).toEqual('2016-11-23');
                expect(controller.request.from_date_type).toEqual('1');
                expect(controller.request.to_date).toEqual('2016-11-28');
                expect(controller.request.to_date_type).toEqual('1');
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

      describe('manager opens leave request popup', function () {
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
            controller.request.fileUploader = { queue: [] };
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
        });

        describe('on submit', function () {
          beforeEach(function () {
            controller.balance.change.amount = controller.request.balance_change;

            spyOn($rootScope, '$emit');
            spyOn(controller.request, 'update').and.callThrough();
            // Pretending original balance change has not been updated
            spyOn(LeaveRequestInstance, 'calculateBalanceChange').and.returnValue(
              $q.resolve({ amount: controller.balance.change.amount }));

            // entitlements are randomly generated so resetting them to positive here
            if (controller.balance.closing < 0) {
              controller.balance.closing = 0;
            }
            // set status id manually as manager would set it on UI
            controller.newStatusOnSave = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '1');

            controller.checkSubmitConditions = jasmine.createSpy('checkSubmitConditions');
            controller.checkSubmitConditions.and.returnValue(true);

            controller.submit();
          });

          describe('if balance change has not been updated', function () {
            beforeEach(function () {
              $scope.$apply();
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
            var confirmFunction;

            beforeEach(function () {
              // Make original balance differ from the calculated balance
              controller.balance.change.amount--;

              spyOn(dialog, 'open').and.callFake(function (params) {
                confirmFunction = params.onConfirm;
              });
              $rootScope.$apply();
            });

            it('does not call update method on instance', function () {
              expect(controller.request.update).not.toHaveBeenCalled();
            });

            it('opens a dialog', function () {
              expect(dialog.open).toHaveBeenCalled();
            });

            describe('on confirm the balance change recalculation', function () {
              beforeEach(function () {
                confirmFunction();
                $rootScope.$apply();
              });

              it('initiates the balance change recalculation', function () {
                expect($rootScope.$emit).toHaveBeenCalledWith(
                  'LeaveRequestPopup::updateBalance');
              });
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

        describe('after contact is selected', function () {
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
