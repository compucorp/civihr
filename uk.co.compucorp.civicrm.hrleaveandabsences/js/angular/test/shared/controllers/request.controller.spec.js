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
    'leave-absences/shared/modules/shared-settings'
  ], function (_, moment, optionGroupMock, mockData) {
    'use strict';

    describe('LeaveRequestCtrl', function () {
      var $log, $rootScope, $ctrl, modalInstanceSpy, $scope, $q, dialog, $controller,
        $provide, sharedSettings, AbsenceTypeAPI, AbsencePeriodAPI, LeaveRequestInstance,
        Contact, ContactAPIMock, EntitlementAPI, LeaveRequestAPI, WorkPatternAPI;
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
        var leaveRequest;

        beforeEach(inject(function () {
          leaveRequest = LeaveRequestInstance.init();
          leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
          initTestController({ leaveRequest: leaveRequest, isSelfRecord: true });
        }));

        it('is called', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('getStatuses returns an array', function () {
          expect($ctrl.getStatuses()).toEqual(jasmine.any(Array));
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
              expect($ctrl.request.type_id).toEqual($ctrl.absenceTypes[0].id);
            });

            it('has nil balance change amount', function () {
              expect($ctrl.balance.change.amount).toEqual(0);
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
                expect($ctrl.request.from_date).toBeUndefined();
                expect($ctrl.request.to_date).toBeUndefined();
              });
            });
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
        });

        describe('canSubmit()', function () {
          beforeEach(function () {
            var status = optionGroupMock.specificValue('hrleaveandabsences_leave_request_status', 'value', '3');
            var leaveRequest = LeaveRequestInstance.init(mockData.findBy('status_id', status));

            leaveRequest.contact_id = CRM.vars.leaveAndAbsences.contactId.toString();
            leaveRequest.fileUploader = { queue: [] };

            initTestController({ isSelfRecord: true, leaveRequest: leaveRequest });
          });

          it('does not allow to submit the leave request without changes', function () {
            expect($ctrl.canSubmit()).toBe(false);
          });

          describe('when a comment is added', function () {
            beforeEach(function () {
              $ctrl.request.comments.push(jasmine.any(Object));
              $ctrl.checkSubmitConditions = jasmine.createSpy('checkSubmitConditions');
              $ctrl.checkSubmitConditions.and.returnValue(true);
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

            initTestController({ isSelfRecord: true, leaveRequest: leaveRequest });
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
          role = 'manager';

          initTestController({ leaveRequest: leaveRequest });
        });

        describe('on initialization', function () {
          var waitingApprovalStatus;

          beforeEach(function () {
            $ctrl.request.fileUploader = { queue: [] };
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

            $ctrl.checkSubmitConditions = jasmine.createSpy('checkSubmitConditions');
            $ctrl.checkSubmitConditions.and.returnValue(true);

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
        var leaveRequest;

        beforeEach(function () {
          role = 'manager';
          leaveRequest = LeaveRequestInstance.init();

          initTestController({ leaveRequest: leaveRequest });
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
              $ctrl.request.contact_id = '204';
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
            expect($ctrl.isMode('edit')).toBeTruthy();
          });

          it('has admin role', function () {
            expect($ctrl.isRole('admin')).toBeTruthy();
          });

          it('does not load contacts', function () {
            expect($ctrl.managedContacts.length).toEqual(0);
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
        var confirmFunction, leaveRequest;

        beforeEach(function () {
          spyOn(dialog, 'open').and.callFake(function (params) {
            confirmFunction = params.onConfirm;
          });
          leaveRequest = LeaveRequestInstance.init();

          initTestController({ leaveRequest: leaveRequest });
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
            $ctrl.request = jasmine.createSpyObj(['delete']);
            $ctrl.request.delete.and.returnValue($q.resolve([]));
            promise = confirmFunction();
          });

          afterEach(function () {
            $rootScope.$apply();
          });

          it('deletes the leave request', function () {
            expect($ctrl.request.delete).toHaveBeenCalled();
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
        var leaveRequest;
        var status = {};

        beforeEach(function () {
          var collectionName = 'hrleaveandabsences_leave_request_status';
          var allStatuses = optionGroupMock.getCollection(collectionName);

          status = _.indexBy(allStatuses, 'name');
          leaveRequest = LeaveRequestInstance.init();
          leaveRequest.status_id = null;

          initTestController({ leaveRequest: leaveRequest });

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
        directiveOptions = directiveOptions || {};

        $ctrl = $controller('RequestCtrl', {
          $scope: $scope,
          $uibModalInstance: modalInstanceSpy,
          directiveOptions: directiveOptions
        });

        $scope.$digest();
      }
    });
  });
})(CRM);
