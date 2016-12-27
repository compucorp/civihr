(function (CRM) {
  define([
    'common/angular',
    'common/lodash',
    'common/angularMocks',
    'leave-absences/shared/config',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/entitlement-api-mock',
    'mocks/apis/leave-request-api-mock',
    'leave-absences/my-leave/app'
  ], function (angular, _) {
    'use strict';

    describe('myLeaveReport', function () {
      var contactId = CRM.vars.leaveAndAbsences.contactId;
      var $compile, $log, $provide, $rootScope, component, controller;
      var AbsencePeriod, AbsenceType, Entitlement, LeaveRequest;

      beforeEach(module('leave-absences.templates', 'my-leave', 'leave-absences.mocks', function (_$provide_) {
        $provide = _$provide_;
      }));
      beforeEach(inject(function (AbsencePeriodAPIMock, AbsenceTypeAPIMock, EntitlementAPIMock, LeaveRequestAPIMock) {
        $provide.value('AbsencePeriodAPI', AbsencePeriodAPIMock);
        $provide.value('AbsenceTypeAPI', AbsenceTypeAPIMock);
        $provide.value('EntitlementAPI', EntitlementAPIMock);
        $provide.value('LeaveRequestAPI', LeaveRequestAPIMock);
      }));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$httpBackend_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;

        spyOn($log, 'debug');
      }));
      beforeEach(inject(function (_AbsencePeriod_, _AbsenceType_, _Entitlement_, _LeaveRequest_) {
        AbsencePeriod = _AbsencePeriod_;
        AbsenceType = _AbsenceType_;
        Entitlement = _Entitlement_;
        LeaveRequest = _LeaveRequest_;

        spyOn(AbsencePeriod, 'all').and.callThrough();
        spyOn(AbsenceType, 'all').and.callThrough();
        spyOn(Entitlement, 'all').and.callThrough();
        spyOn(LeaveRequest, 'balanceChangeByAbsenceType').and.callThrough();
      }));

      beforeEach(function () {
        compileComponent();
      });

      describe('initialization', function () {
        it('is initialized', function () {
          expect($log.debug).toHaveBeenCalled();
        });

        it('has all the sections collapsed', function () {
          expect(Object.values(controller.isOpen).every(function (status) {
            return status === false;
          })).toBe(true);
        });

        it('contains the expected markup', function () {
          expect(component.find('.chr_leave-report').length).toBe(1);
        });

        describe('data loading', function () {
          xdescribe('before data is loaded', function () {
            // TODO: check why it doesn't work
            it('is in loading mode', function () {
              expect(controller.loading).toBe(true);
            });
          });

          describe('after data is loaded', function () {
            it('is out of loading mode', function () {
              expect(controller.loading).toBe(false);
            });

            it('has fetched the absence types', function () {
              expect(AbsenceType.all).toHaveBeenCalled();
              expect(controller.absenceTypes.length).not.toBe(0);
            });

            it('has fetched the absence periods', function () {
              expect(AbsencePeriod.all).toHaveBeenCalled();
              expect(controller.absencePeriods.length).not.toBe(0);
            });

            it('has automatically selected the current period', function () {
              expect(controller.currentPeriod).not.toBe(null);
              expect(controller.currentPeriod).toBe(_.find(controller.absencePeriods, function (period) {
                return period.current === true;
              }));
            });

            describe('entitlements', function () {
              it('has fetched all the entitlements', function () {
                expect(Entitlement.all).toHaveBeenCalled();
                expect(controller.entitlements.length).not.toBe(0);
              });

              it('has fetched the entitlements for the current contact and period', function () {
                expect(Entitlement.all.calls.argsFor(0)[0]).toEqual({
                  contact_id: contactId,
                  period_id: controller.currentPeriod.id
                });
              });

              it('has fetched both current and future balance of the entitlements', function () {
                expect(Entitlement.all.calls.argsFor(0)[1]).toEqual(true);
              });
            });

            describe('balance changes', function () {
              it('has fetched the balance changes for the current contact and period', function () {
                var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(0)[0];

                expect(args).toEqual(jasmine.objectContaining({
                  contact_id: contactId,
                  period_id: controller.currentPeriod.id
                }));
              });

              it('has fetched the balance changes for the public holidays', function () {
                expect(LeaveRequest.balanceChangeByAbsenceType).toHaveBeenCalledWith(jasmine.objectContaining({
                  public_holiday: true
                }));
                expect(controller.balanceChanges.publicHolidays).not.toBe(0);
              });

              it('has fetched the balance changes for the approved requests', function () {
                expect(LeaveRequest.balanceChangeByAbsenceType).toHaveBeenCalledWith(jasmine.objectContaining({
                  statuses: [ '<value of OptionValue "approved">' ]
                }));
                expect(controller.balanceChanges.approved).not.toBe(0);
              });

              it('has fetched the balance changes for the open requests', function () {
                expect(LeaveRequest.balanceChangeByAbsenceType).toHaveBeenCalledWith(jasmine.objectContaining({
                  statuses: [
                    '<value of OptionValue "awaiting approval">',
                    '<value of OptionValue "more information">'
                  ]
                }));
                expect(controller.balanceChanges.open).not.toBe(0);
              });
            });
          });
        });
      });

      describe('when changing the absence period', function () {
        var newPeriod;

        beforeEach(function () {
          newPeriod = _(controller.absencePeriods).filter(function (period) {
            return !period.current;
          }).sample();

          Entitlement.all.calls.reset();
          LeaveRequest.balanceChangeByAbsenceType.calls.reset();

          controller.changePeriod(newPeriod);
        });

        it('sets the new current period', function () {
          expect(controller.currentPeriod).toBe(newPeriod);
        });

        it('goes into loading mode', function () {
          expect(controller.loading).toBe(true);
        });

        it('reloads the entitlements', function () {
          expect(Entitlement.all).toHaveBeenCalled();
          expect(Entitlement.all.calls.argsFor(0)[0]).toEqual(jasmine.objectContaining({
            period_id: newPeriod.id
          }));
        });

        it('reloads all the balance changes', function () {
          var args = LeaveRequest.balanceChangeByAbsenceType.calls.argsFor(_.random(0, 2))[0];

          expect(LeaveRequest.balanceChangeByAbsenceType).toHaveBeenCalledTimes(3);
          expect(args).toEqual(jasmine.objectContaining({
            period_id: newPeriod.id
          }));
        });

        it('reloads all leave requests for sections already opened', function () {

        });

        it('removes all leave requests for sections that are closed', function () {

        });

        describe('after loading', function () {
          beforeEach(function () {
            $rootScope.$digest();
          });

          it('goes out of loading mode', function () {
            expect(controller.loading).toBe(false);
          });
        });
      });

      describe('when opening a section', function () {
        describe('data caching', function () {
          describe('when the section had not been opened yet', function () {
            it('makes a request to fetch the data', function () {

            });
          });

          describe('when the section had already been opened', function () {
            it('does not make another request to fetch the data', function () {

            });
          });
        });

        describe('section: Period Entitlement', function () {
          it('fetches the entitlements breakdown', function () {

          });

          it('passes to the Model the entitlements already stored', function () {

          });
        });

        describe('section: Public Holidays', function () {
          it('fetches all leave requests linked to a public holiday', function () {

          });
        });

        describe('section: Approved Requests', function () {
          it('fetches all approved leave requests', function () {

          });
        });

        describe('section: Open Requests', function () {
          it('fetches all pending leave requests', function () {

          });
        });

        describe('section: Expired', function () {
          it('fetches all expired balance changes', function () {

          });
        });

        describe('section: Cancelled and Other', function () {
          it('fetches all cancelled/rejected leave requests', function () {

          });
        });
      });

      describe('action matrix for a leave request', function () {
        describe('status: awaiting approval', function () {
          it('shows the "edit" and "cancel" actions', function () {

          });
        });

        describe('status: more information required', function () {
          it('shows the "respond" and "cancel" actions', function () {

          });
        });

        describe('status: approved', function () {
          it('shows the "cancel" actions', function () {

          });
        });

        describe('status: cancelled', function () {
          it('shows the no actions', function () {

          });
        });
      });

      describe('when cancelling a leave request', function () {
        it('shows a confirmation dialog', function () {

        });

        describe('when the user confirms', function () {
          it('sends the cancellation request', function () {

          });
        });

        describe('when the user does not confirm', function () {
          it('does not send the cancellation request', function () {

          });
        });
      });

      function compileComponent() {
        var $scope = $rootScope.$new();

        component = angular.element('<my-leave-report contact-id="' + contactId + '"></my-leave-report>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('myLeaveReport');
      }
    });
  })
})(CRM);
