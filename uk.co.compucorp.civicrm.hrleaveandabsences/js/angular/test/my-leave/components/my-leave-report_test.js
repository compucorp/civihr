(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/my-leave/app'
  ], function (angular) {
    'use strict';

    describe('myLeaveReport', function () {
      var $compile, $log, $rootScope, component, controller;

      beforeEach(module('leave-absences.templates', 'my-leave'));
      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;

        spyOn($log, 'debug');

        compileComponent();
      }));

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
          describe('before data is loaded', function () {
            it('is in loading mode', function () {

            });
          });

          describe('after data is loaded', function () {
            it('is out of loading mode', function () {

            });

            it('has fetched the absence periods', function () {

            });

            it('has fetched the absence types', function () {

            });

            it('has fetched all the entitlements', function () {

            });

            it('has fetched the balance changes', function () {

            });
          });
        });
      });

      describe('when changing the absence period', function () {
        it('reloads the entitlements', function () {

        });

        it('reloads the balance changes', function () {

        });

        it('reloads all leave requests for sections already opened', function () {

        });

        it('removes all leave requests for sections that are closed', function () {

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
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<my-leave-report contact-id="' + contactId + '"></my-leave-report>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('myLeaveReport');
      }
    });
  })
})(CRM);
