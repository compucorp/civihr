/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/option-group.data',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/mocks/data/leave-request.data',
  'common/services/notification.service',
  'common/services/pub-sub',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/leave-request-api-mock',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/shared/config',
  'leave-absences/shared/services/leave-request.service',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, optionGroupMock, absenceTypeData, leaveRequestData) {
  'use strict';

  describe('leaveRequestActions', function () {
    var $componentController, $log, $provide, $q, $rootScope, controller,
      LeaveRequestInstance, dialog, sharedSettings, role, leaveRequest,
      LeavePopup, LeaveRequestService, notification, pubSub;
    var absenceTypes = _.indexBy(absenceTypeData.all().values, 'id');
    var leaveRequestStatuses = _.indexBy(optionGroupMock.getCollection('hrleaveandabsences_leave_request_status'), 'value');

    beforeEach(module('leave-absences.mocks', 'manager-leave', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('api.optionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject(['shared-settings', function (_sharedSettings_) {
      sharedSettings = _sharedSettings_;
    }]));

    beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_,
      _dialog_, _LeaveRequestInstance_, _LeavePopup_, _LeaveRequestService_, _notificationService_, _pubSub_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      dialog = _dialog_;
      LeaveRequestInstance = _LeaveRequestInstance_;
      LeavePopup = _LeavePopup_;
      LeaveRequestService = _LeaveRequestService_;
      notification = _notificationService_;
      pubSub = _pubSub_;
    }));

    beforeEach(function () {
      window.alert = function () {}; // prevent alert from being logged in console

      spyOn($log, 'debug');
      spyOn(pubSub, 'publish').and.callThrough();
    });

    beforeEach(function () {
      role = 'staff';
      leaveRequest = getRequest();

      makeRequestExpired(leaveRequest, false);
      compileComponent();
    });

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('when leave request status is "awaiting for approval"', function () {
      beforeEach(function () {
        leaveRequest = getRequest('leave', 'awaitingApproval');
      });

      describe('when the user is admin', function () {
        beforeEach(function () {
          role = 'admin';

          compileComponent();
        });

        it('shows actions "Respond", "Approve", "Reject", "Cancel" and "Delete"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
        });
      });

      describe('when the user is manager', function () {
        beforeEach(function () {
          role = 'manager';

          compileComponent();
        });

        it('shows actions "Respond", "Approve", "Reject" and "Cancel"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['respond', 'approve', 'reject', 'cancel']);
        });
      });

      describe('when the user is staff', function () {
        beforeEach(function () {
          role = 'staff';

          compileComponent();
        });

        it('shows actions "Edit", "Cancel"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit', 'cancel']);
        });
      });
    });

    describe('when leave request status is "more information required"', function () {
      beforeEach(function () {
        leaveRequest = getRequest('leave', 'moreInformationRequired');
      });

      describe('when the user is admin', function () {
        beforeEach(function () {
          role = 'admin';

          compileComponent();
        });

        it('shows actions "Edit", "Cancel" and "Delete"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit', 'cancel', 'delete']);
        });
      });

      describe('when the user is manager', function () {
        beforeEach(function () {
          role = 'manager';

          compileComponent();
        });

        it('shows actions "Edit" and "Cancel"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit', 'cancel']);
        });
      });

      describe('when the user is staff', function () {
        beforeEach(function () {
          role = 'staff';

          compileComponent();
        });

        it('shows actions "Respond" and "Cancel"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['respond', 'cancel']);
        });
      });
    });

    describe('when leave request status is "approved"', function () {
      beforeEach(function () {
        leaveRequest = getRequest('leave', 'approved');
      });

      describe('when the user is admin', function () {
        beforeEach(function () {
          role = 'admin';

          compileComponent();
        });

        it('shows actions "Edit", "Cancel" and "Delete"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit', 'cancel', 'delete']);
        });
      });

      describe('when the user is manager', function () {
        beforeEach(function () {
          role = 'manager';

          compileComponent();
        });

        it('shows actions "Edit"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit']);
        });
      });

      describe('when the user is staff', function () {
        beforeEach(function () {
          role = 'staff';

          compileComponent();
        });

        it('shows actions "View" and "Cancel"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['view', 'cancel']);
        });
      });
    });

    describe('when leave request status is "rejected"', function () {
      beforeEach(function () {
        leaveRequest = getRequest('leave', 'rejected');
      });

      describe('when the user is admin', function () {
        beforeEach(function () {
          role = 'admin';

          compileComponent();
        });

        it('shows actions "Edit", "Cancel" and "Delete"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit', 'cancel', 'delete']);
        });
      });

      describe('when the user is manager', function () {
        beforeEach(function () {
          role = 'manager';

          compileComponent();
        });

        it('shows actions "Edit"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit']);
        });
      });

      describe('when the user is staff', function () {
        beforeEach(function () {
          role = 'staff';

          compileComponent();
        });

        it('shows actions "View" and "Cancel"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['view', 'cancel']);
        });
      });
    });

    describe('when leave request status is "cancelled"', function () {
      beforeEach(function () {
        leaveRequest = getRequest('leave', 'cancelled');
      });

      describe('when the user is admin', function () {
        beforeEach(function () {
          role = 'admin';

          compileComponent();
        });

        it('shows actions "Edit" and "Delete"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit', 'delete']);
        });
      });

      describe('when the user is manager', function () {
        beforeEach(function () {
          role = 'manager';

          compileComponent();
        });

        it('shows actions "Edit"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['edit']);
        });
      });

      describe('when the user is staff', function () {
        beforeEach(function () {
          role = 'staff';

          compileComponent();
        });

        it('shows actions "View"', function () {
          expect(flattenActions(controller.allowedActions)).toEqual(['view']);
        });
      });
    });

    describe('when leave request has TOIL type', function () {
      beforeEach(function () {
        leaveRequest = getRequest('toil', 'awaitingApproval');
      });

      describe('when not expired', function () {
        beforeEach(function () {
          makeRequestExpired(leaveRequest, false);
        });

        describe('when the user is admin', function () {
          beforeEach(function () {
            role = 'admin';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is manager', function () {
          beforeEach(function () {
            role = 'manager';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is staff', function () {
          beforeEach(function () {
            role = 'staff';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });
      });

      describe('when expired', function () {
        beforeEach(function () {
          makeRequestExpired(leaveRequest, true);
        });

        describe('when the user is admin', function () {
          beforeEach(function () {
            role = 'admin';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is manager', function () {
          beforeEach(function () {
            role = 'manager';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is staff', function () {
          beforeEach(function () {
            role = 'staff';

            compileComponent();
          });

          it('does not include "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(false);
          });
        });
      });
    });

    describe('when leave request has Sickness type', function () {
      beforeEach(function () {
        leaveRequest = getRequest('sick', 'awaitingApproval');
      });

      describe('when not expired', function () {
        beforeEach(function () {
          makeRequestExpired(leaveRequest, false);
        });

        describe('when the user is admin', function () {
          beforeEach(function () {
            role = 'admin';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is manager', function () {
          beforeEach(function () {
            role = 'manager';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is staff', function () {
          beforeEach(function () {
            role = 'staff';

            compileComponent();
          });

          it('does not include "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(false);
          });
        });
      });

      describe('when expired', function () {
        beforeEach(function () {
          makeRequestExpired(leaveRequest, true);
        });

        describe('when the user is admin', function () {
          beforeEach(function () {
            role = 'admin';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is manager', function () {
          beforeEach(function () {
            role = 'manager';

            compileComponent();
          });

          it('includes "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(true);
          });
        });

        describe('when the user is staff', function () {
          beforeEach(function () {
            role = 'staff';

            compileComponent();
          });

          it('does not include "Cancel" action', function () {
            expect(_.includes(flattenActions(controller.allowedActions), 'cancel')).toBe(false);
          });
        });
      });
    });

    describe('when the leave request is a public holiday', function () {
      beforeEach(function () {
        leaveRequest.request_type = 'public_holiday';
      });

      describe('when the user is an admin', function () {
        beforeEach(function () {
          role = 'admin';

          compileComponent();
        });

        it('includes the "Delete" action', function () {
          expect(_.includes(flattenActions(controller.allowedActions), 'delete')).toBe(true);
        });
      });

      describe('when the user is a manager', function () {
        beforeEach(function () {
          role = 'manager';

          compileComponent();
        });

        it('does not include the "Delete" action', function () {
          expect(_.includes(flattenActions(controller.allowedActions), 'delete')).toBe(false);
        });
      });

      describe('when the user is a staff', function () {
        beforeEach(function () {
          role = 'staff';

          compileComponent();
        });

        it('does not include the "Delete" action', function () {
          expect(_.includes(flattenActions(controller.allowedActions), 'delete')).toBe(false);
        });
      });
    });

    describe('when the user wants to change status of leave request', function () {
      // Any action, role or request could be specified here
      var action = 'approve';

      beforeEach(function () {
        // Any role or request could be specified here
        role = 'admin';
        leaveRequest = getRequest();
        compileComponent();
        spyOn($rootScope, '$emit');
      });

      describe('basic tests', function () {
        beforeEach(function () {
          spyOn(controller.leaveRequest, 'checkIfBalanceChangeNeedsRecalculation')
            .and.callThrough();

          controller.action(action);
          $rootScope.$digest();
        });

        it('checks if the balance has been changed', function () {
          expect(controller.leaveRequest.checkIfBalanceChangeNeedsRecalculation)
            .toHaveBeenCalled();
        });
      });

      describe('when balance change has not been changed', function () {
        beforeEach(function () {
          spyOn(controller.leaveRequest, 'checkIfBalanceChangeNeedsRecalculation')
            .and.returnValue($q.resolve(false));
          resolveDialogWith(null);
          controller.action(action);
          $rootScope.$digest();
        });

        it('shows a loading confirmation dialog', function () {
          expect(dialog.open).toHaveBeenCalledWith(jasmine.objectContaining({
            loading: true
          }));
        });

        it('does not emit an event', function () {
          expect($rootScope.$emit).not.toHaveBeenCalled();
        });

        describe('when the user confirms the action', function () {
          describe('when the action is successfully executed', function () {
            beforeEach(function () {
              spyOn(leaveRequest, action).and.returnValue($q.resolve());
              resolveDialogWith(true);
              controller.action(action);
              $rootScope.$digest();
            });

            it('emits an event', function () {
              expect(pubSub.publish)
                .toHaveBeenCalledWith('LeaveRequest::statusUpdate', {
                  status: action,
                  leaveRequest: leaveRequest
                });
            });
          });

          describe('when the action is rejected by server', function () {
            beforeEach(function () {
              spyOn(leaveRequest, action).and.returnValue($q.reject());
              spyOn(notification, 'error').and.callThrough();
              resolveDialogWith(true);
              controller.action(action);
              $rootScope.$digest();
            });

            it('does not emit an event', function () {
              expect(pubSub.publish).not.toHaveBeenCalled();
            });

            it('shows a notification', function () {
              expect(notification.error).toHaveBeenCalled();
            });
          });
        });
      });

      describe('when balance change has been changed', function () {
        var proceedWithBalanceChangeRecalculation;

        beforeEach(function () {
          spyOn(controller.leaveRequest, 'checkIfBalanceChangeNeedsRecalculation')
            .and.returnValue($q.resolve(true));
          spyOn(dialog, 'open').and.callFake(function (params) {
            params.optionsPromise().then(function (props) {
              proceedWithBalanceChangeRecalculation = props.onCloseAfterConfirm;
            });
          });
          spyOn(LeaveRequestService,
            'getBalanceChangeRecalculationPromptOptions').and.callThrough();
          controller.action(action);
          $rootScope.$digest();
        });

        it('prompts if user would like to proceed with balance change recalculation', function () {
          expect(LeaveRequestService.getBalanceChangeRecalculationPromptOptions)
            .toHaveBeenCalled();
          expect(dialog.open).toHaveBeenCalled();
        });

        describe('when user proceeds with balance change recalculation', function () {
          beforeEach(function () {
            spyOn(LeavePopup, 'openModal');
            proceedWithBalanceChangeRecalculation();
            $rootScope.$digest();
          });

          it('opens a leave request modal with this request', function () {
            expect(LeavePopup.openModal).toHaveBeenCalledWith(
              leaveRequest, leaveRequest.request_type, leaveRequest.contact_id, jasmine.any(Boolean),
              true);
          });
        });
      });

      describe('when the user wants either to cancel, reject or delete the leave request', function () {
        beforeEach(function () {
          spyOn(controller.leaveRequest, 'checkIfBalanceChangeNeedsRecalculation')
            .and.callThrough();
        });

        ['cancel', 'reject', 'delete'].forEach(function (action) {
          beforeEach(function () {
            controller.action(action);
            $rootScope.$digest();
          });

          it('skips checking of the balance change', function () {
            expect(controller.leaveRequest.checkIfBalanceChangeNeedsRecalculation)
              .not.toHaveBeenCalled();
          });
        });
      });

      describe('when request is TOIL', function () {
        beforeEach(function () {
          leaveRequest = getRequest('toil', 'awaitingApproval');

          compileComponent();
          spyOn(controller.leaveRequest, 'checkIfBalanceChangeNeedsRecalculation')
            .and.callThrough();
          controller.action('approve');
          $rootScope.$digest();
        });

        it('skips checking of the balance change', function () {
          expect(controller.leaveRequest.checkIfBalanceChangeNeedsRecalculation)
            .not.toHaveBeenCalled();
        });
      });
    });

    describe('openLeavePopup()', function () {
      var event;
      var leaveRequest = { key: 'value' };
      var leaveType = 'some_leave_type';
      var selectedContactId = '101';
      var isSelfRecord = true;

      beforeEach(function () {
        event = jasmine.createSpyObj('event', ['stopPropagation']);
        spyOn(LeavePopup, 'openModal');
        controller.openLeavePopup(event, leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('opens the leave request popup', function () {
        expect(LeavePopup.openModal).toHaveBeenCalledWith(leaveRequest, leaveType, selectedContactId, isSelfRecord);
      });

      it('stops the event from propagating', function () {
        expect(event.stopPropagation).toHaveBeenCalled();
      });
    });

    /**
     * Flattens actions object into an array of actions keys
     *
     * @param  {Object} actions - object, containing actions keys
     * @return {Array} actions keys
     */
    function flattenActions (actions) {
      return _.map(actions, function (action) {
        return action.key;
      });
    }

    /**
     * Returns a mocked request by a given absence type
     *
     * @param  {string} type - leave|toil|sick, optional
     * @param  {string} status - according to sharedSettings.statusNames, optional
     * @return {LeaveRequestInstance}
     */
    function getRequest (type, status) {
      type = type || 'leave';

      var map = { leave: '1', toil: '2', sick: '3' };
      var statuses = _.indexBy(optionGroupMock.getCollection('hrleaveandabsences_leave_request_status'), 'name');
      var leaveRequestMock = _.find(leaveRequestData.all().values, { type_id: map[type] });
      var leaveRequest = LeaveRequestInstance.init(leaveRequestMock, true);

      if (status) {
        leaveRequest.status_id = statuses[sharedSettings.statusNames[status]].value;
      }

      return leaveRequest;
    }

    /**
     * Mocks the current time in a such way
     * that a request becomes expired (or not expired)
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {boolean} isExpired
     */
    function makeRequestExpired (leaveRequest, isExpired) {
      var dateMock = moment(leaveRequest.from_date).add((isExpired ? 1 : -1), 'days').toDate();
      jasmine.clock().mockDate(dateMock);
    }

    /**
     * Spies on dialog.open() method and resolves it with the given value
     *
     * @param {any} value
     */
    function resolveDialogWith (value) {
      var spy;

      if (typeof dialog.open.calls !== 'undefined') {
        spy = dialog.open;
      } else {
        spy = spyOn(dialog, 'open');
      }

      spy.and.callFake(function (options) {
        if (!value) {
          return;
        }

        return $q.resolve()
          .then(options.optionsPromise)
          .then(function (props) {
            return props && props.onConfirm ? props.onConfirm() : null;
          })
          .then(options.onConfirm)
          .then(function () {
            return value;
          });
      });
    }

    function compileComponent () {
      controller = $componentController('leaveRequestActions', null, {
        leaveRequest: leaveRequest,
        role: role,
        absenceTypes: absenceTypes,
        leaveRequestStatuses: leaveRequestStatuses
      });
    }
  });
});
