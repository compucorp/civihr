/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/moment',
  'mocks/data/option-group-mock-data',
  'mocks/data/absence-type-data',
  'mocks/data/leave-request-data',
  'leave-absences/shared/config',
  'leave-absences/manager-leave/app'
], function (angular, _, moment, optionGroupMock, absenceTypeData, leaveRequestData) {
  'use strict';

  describe('leaveRequestActionDropdown', function () {
    var $componentController, $log, $q, $rootScope, vm, LeaveRequestInstance, dialog, sharedSettings, role, request;
    var absenceTypes = _.indexBy(absenceTypeData.all().values, 'id');
    var requestStatuses = _.indexBy(optionGroupMock.getCollection('hrleaveandabsences_leave_request_status'), 'value');

    beforeEach(module('manager-leave'));

    beforeEach(inject(['shared-settings', function (_sharedSettings_) {
      sharedSettings = _sharedSettings_;
    }]));

    beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_, _dialog_, _LeaveRequestInstance_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      dialog = _dialog_;
      LeaveRequestInstance = _LeaveRequestInstance_;
    }));

    beforeEach(function () {
      spyOn($log, 'debug');
    });

    beforeEach(function () {
      role = 'staff';
      request = LeaveRequestInstance.init(leaveRequestData.all().values[0], true);

      compileComponent();
    });

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('for admin', function () {
      beforeEach(function () {
        role = 'admin';
        request = getRequestByType('leave');

        compileComponent();
      });

      it('has correct role', function () {
        expect(vm.role).toBe(role);
      });

      describe('for Leave request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel', 'delete']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });
      });

      describe('for TOIL request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel', 'delete']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });
      });

      describe('for Sickness request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel', 'delete']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel', 'delete']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view', 'delete']);
            });
          });
        });
      });
    });

    describe('for manager', function () {
      beforeEach(function () {
        role = 'manager';
        request = getRequestByType('leave');

        compileComponent();
      });

      it('has correct role', function () {
        expect(vm.role).toBe(role);
      });

      describe('for Leave request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });
      });

      describe('for TOIL request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });
      });

      describe('for Sickness request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject', 'cancel']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond', 'approve', 'reject']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit', 'cancel']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });
      });
    });

    describe('for staff', function () {
      beforeEach(function () {
        role = 'staff';
        request = getRequestByType('leave');

        compileComponent();
      });

      it('has correct role', function () {
        expect(vm.role).toBe(role);
      });

      describe('for Leave request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });
      });

      describe('for TOIL request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });
      });

      describe('for Sickness request', function () {
        beforeEach(function () {
          request = getRequestByType('leave');
        });

        describe('when status is "awaiting for approval"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'awaitingApproval');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['edit']);
            });
          });
        });

        describe('when status is "more information required"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'moreInformationRequired');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['respond']);
            });
          });
        });

        describe('when status is "approved"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'approved');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "rejected"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'rejected');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });

        describe('when status is "cancelled"', function () {
          beforeEach(function () {
            setRequestStatus(request, 'cancelled');
          });

          describe('when not expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, false);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });

          describe('when expired', function () {
            beforeEach(function () {
              makeRequestExpired(request, true);
              compileComponent();
            });

            it('allows correct actions', function () {
              expect(vm.actions).toEqual(['view']);
            });
          });
        });
      });
    });

    describe('wants to change status of leave request', function () {
      // Any action, role or request could be specified here
      var action = 'approve';

      beforeEach(function () {
        // Any role or request could be specified here
        role = 'admin';
        request = getRequestByType('leave');

        spyOn(request, action).and.returnValue($q.resolve());
        resolveDialogWith(null);
        vm.act(action);
        $rootScope.$digest();
        compileComponent();
      });

      it('sees a confirmation dialog', function () {
        expect(dialog.open).toHaveBeenCalled();
      });

      describe('confirms the action', function () {
        beforeEach(function () {
          resolveDialogWith(true);
          spyOn($rootScope, '$emit');
          vm.act(action);
          $rootScope.$digest();
        });

        it('other controllers are notified', function () {
          expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::edit', jasmine.any(Object));
        });
      });
    });

    describe('wants to delete leave request', function () {
      var action = 'delete';

      beforeEach(function () {
        // Any role or request could be specified here
        role = 'admin';
        request = getRequestByType('leave');

        spyOn(request, action).and.returnValue($q.resolve());
        resolveDialogWith(null);
        vm.act(action);
        $rootScope.$digest();
        compileComponent();
      });

      it('sees a confirmation dialog', function () {
        expect(dialog.open).toHaveBeenCalled();
      });

      describe('confirms the action', function () {
        beforeEach(function () {
          resolveDialogWith(true);
          spyOn($rootScope, '$emit');
          vm.act(action);
          $rootScope.$digest();
        });

        it('other controllers are notified', function () {
          expect($rootScope.$emit).toHaveBeenCalledWith('LeaveRequest::deleted', jasmine.any(Object));
        });
      });
    });

    /**
     * Returns a mocked request by a given absence type
     *
     * @param  {string} type - leave|toil|sick
     * @return {LeaveRequestInstance}
     */
    function getRequestByType (type) {
      var map = { leave: '1', toil: '2', sick: '3' };
      var requestMock = _.find(leaveRequestData.all().values, { type_id: map[type] });

      return LeaveRequestInstance.init(requestMock, true);
    }

    /**
     * Mocks the current time in a such way
     * that a request becomes expired (or not expired)
     *
     * @param {LeaveRequestInstance} request
     * @param {boolean} isExpired
     */
    function makeRequestExpired (request, isExpired) {
      var dateMock = moment(request.from_date).add((isExpired ? 1 : -1), 'days').toDate();
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
        return $q.resolve()
          .then(function () {
            return options.onConfirm && value ? options.onConfirm() : null;
          })
          .then(function () {
            return value;
          });
      });
    }

    /**
    * Sets a status of a given request
    *
    * @param {LeaveRequestInstance} request
    * @param {string} status
     */
    function setRequestStatus (request, status) {
      var statuses = _.indexBy(optionGroupMock.getCollection('hrleaveandabsences_leave_request_status'), 'name');

      request.status_id = statuses[sharedSettings.statusNames[status]].value;
    }

    function compileComponent () {
      vm = $componentController('leaveRequestActionDropdown', null, {
        request: request,
        role: role,
        absenceTypes: absenceTypes,
        requestStatuses: requestStatuses
      });
    }
  });
});
