(function (CRM) {
  define([
    'common/lodash',
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'common/mocks/services/hr-settings-mock',
    'mocks/apis/absence-period-api-mock',
    'mocks/apis/absence-type-api-mock',
    'mocks/apis/entitlement-api-mock',
    'mocks/apis/work-pattern-api-mock',
    'mocks/apis/leave-request-api-mock',
    'mocks/apis/option-group-api-mock',
    'leave-absences/shared/directives/leave-request-popup',
    'leave-absences/shared/models/absence-period-model',
    'leave-absences/shared/models/absence-type-model',
    'leave-absences/shared/models/entitlement-model',
    'leave-absences/shared/models/calendar-model',
    'leave-absences/shared/models/leave-request-model',
    'leave-absences/shared/models/public-holiday-model',
    //'common/services/angular-date/date-format',
    'leave-absences/shared/components/leave-request-popup-controller',
    'leave-absences/shared/models/instances/leave-request-instance',
  ], function (_, angular) {
    'use strict';

    describe('LeaveRequestPopupCtrl', function () {
      var $compile, $log, $rootScope, controller, directive, $uibModal, modalInstanceSpy, $scope, $q,
        $controllerScope, $provide, DateFormat, LeaveRequestInstance;

      beforeEach(module('leave-absences.templates', 'leave-absences.directives', 'leave-absences.components',
        'leave-absences.models', 'leave-absences.mocks', 'common.mocks', 'leave-absences.models.instances',
        function (_$provide_) {
          $provide = _$provide_;
        }));

      beforeEach(inject(function (_AbsencePeriodAPIMock_, _HR_settingsMock_, _AbsenceTypeAPIMock_,
        _EntitlementAPIMock_, _WorkPatternAPI_, _LeaveRequestAPIMock_, _OptionGroupAPIMock_, _PublicHolidayAPIMock_) {
        $provide.value('AbsencePeriodAPI', _AbsencePeriodAPIMock_);
        $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
        $provide.value('EntitlementAPI', _EntitlementAPIMock_);
        $provide.value('WorkPatternAPI', _WorkPatternAPI_);
        $provide.value('HR_settings', _HR_settingsMock_);
        $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
        $provide.value('api.optionGroup', _OptionGroupAPIMock_);
        $provide.value('PublicHolidayAPI', _PublicHolidayAPIMock_);
      }));

      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_, _$uibModal_, _LeaveRequestInstance_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;
        $uibModal = _$uibModal_;
        LeaveRequestInstance = _LeaveRequestInstance_;
        spyOn($log, 'debug');
        modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss']);
      }));

      beforeEach(inject(function (_$controller_, _$rootScope_, _$q_) {
        $scope = _$rootScope_.$new();
        $q = _$q_;

        controller = _$controller_('LeaveRequestPopupCtrl', {
          $scope: $scope,
          $uibModalInstance: modalInstanceSpy,
          baseData: {contactId: 202}
        });
        $scope.$digest();
      }));

      it('is called', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('dialog is open', function () {

        it('has expected markup', function () {
          //todo or remove
        });

        describe('initialize leave request', function () {

          beforeEach(function () {
            $scope.$digest();
          });

          it('creates an instance of leave request', function(){
            console.log('leaveRequest', controller.leaveRequest);
            expect(controller.leaveRequest).toBeDefined();
          });

          it('with current absence period', function () {
            console.log('period', controller.period);
            expect(controller.period).toBeDefined();
          });

          it('with absence types', function () {
            console.log('absenceTypes', controller.absenceTypes);
            expect(controller.absenceTypes).toBeDefined(); 
          });

          it('with work pattern', function () {
            console.log('calendar', controller.calendar);
            expect(controller.calendar).toBeDefined();
            expect(controller.calendar.days).toBeDefined();
          });

          it('with balance changes', function () {
            console.log('balance', controller.balance);
            expect(controller.balance).toBeDefined();
            expect(controller.balance.opening).toBeDefined();
            expect(controller.balance.change).toBeDefined();
            expect(controller.balance.closing).toBeDefined();
          });
        });

        describe('when cancels dialog (clicks on X)', function () {

          beforeEach(function () {
            controller.cancel();
          });

          it('closes model', function () {
            expect(modalInstanceSpy.dismiss).toHaveBeenCalled();
          });
        });
      });

      describe('leave absence types', function () {

        describe('view all', function () {

          beforeEach(function () {
            $scope.$digest();
          });

          it('shows all items', function () {
            console.log('absenceTypes',controller.absenceTypes);
            expect(controller.absenceTypes.length).toEqual(6);
          });

          it('selects the current period', function () {
            console.log('period',controller.period);
            expect(controller.period.current).toBeTruthy();
          });

        });

        describe('change selection', function () {
          var beforeChangeAbsenceType, afterChangeAbsenceType, beforeBalanceChange, afterBalanceChange;

          beforeEach(function(){
            beforeChangeAbsenceType = controller.selectedAbsenceType;
            beforeBalanceChange = controller.balance;
            controller.selectedAbsenceType = controller.absenceTypes[1];

            controller.onAbsenceTypeChange();
            afterChangeAbsenceType =  controller.selectedAbsenceType;
            afterBalanceChange = controller.balance;
            $scope.$digest();
          });

          it('selects another absence type', function () {
            expect(beforeChangeAbsenceType).not.toEqual(afterChangeAbsenceType);
          });

          it('balance is changed', function(){
            expect(beforeBalanceChange).not.toEqual(afterBalanceChange);
          });
        });
      });

      describe('number of days', function () {

        describe('single', function () {

          it('selects single day', function () {

          });

          it('has expected markup', function () {

          });

        });

        describe('multiple', function () {

          it('selects multiple days', function () {

          });

          it('has expected markup', function () {

          });
        });
      });

      describe('calendar', function () {

        describe('from date', function () {

          it('has default as current date', function () {

          });

          it('can change date', function () {

          });

        });

        describe('to date', function () {

          it('has default as current date', function () {

          });

          it('can change date', function () {

          });

        });
      });

      describe('day types', function () {

        describe('from', function () {

          it('has default', function () {

          });

          it('allows changing selection', function () {

          });

        });

        describe('to', function () {

          it('has default', function () {

          });

          it('allows changing selection', function () {

          });

        });
      });

      describe('calculate balance', function () {

        describe('opening', function () {

          it('has default', function () {

          });

        });

        describe('change', function () {

          it('has default', function () {

          });

          describe('from date changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });

          describe('from day type changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });

          describe('to date changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });

          describe('to day type changed', function () {

            it('updates change', function () {

            });

            it('updates closing balance', function () {

            });
          });
        });

        describe('details', function () {

          describe('hide', function () {

            it('collapses details part', function () {

            });
          });

          describe('show', function () {

            it('expands details part', function () {

            });

            it('has expected markup', function () {

            });

            describe('pagination', function () {

              it('has default page selected', function () {

              });

              it('change selected page', function () {

              });
            });
          });
        });
      });

      describe('user saves leave request', function () {

        it('validates leave request', function () {

        });

        it('updates leave request', function () {

        });
      });

      describe('user cancels selection', function () {

        it('model closes', function () {

        });

        it('back button is clicked', function () {

        });
      });
    });
  });
})(CRM);
