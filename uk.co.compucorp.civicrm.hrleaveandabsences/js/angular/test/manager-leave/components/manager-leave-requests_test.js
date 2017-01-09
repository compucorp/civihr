(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'leave-absences/shared/config',
    'leave-absences/manager-leave/app'
  ], function (angular) {
    'use strict';

    describe('managerLeaveReport', function () {
      var $compile, $log, $rootScope, component, controller;

      beforeEach(module('leave-absences.templates', 'manager-leave'));
      beforeEach(inject(function (_$compile_, _$log_, _$rootScope_) {
        $compile = _$compile_;
        $log = _$log_;
        $rootScope = _$rootScope_;

        spyOn($log, 'debug');

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      it('the filter section is closed', function () {

      });

      it('the filters are reset', function () {

      });

      it('pagination is set to page 1', function () {

      });

      describe('data loading', function () {

        describe('before loading starts', function () {

          it('loading should be hidden', function () {

          });

          it('leave requests are empty', function () {

          });

          it('absencePeriods are empty', function () {

          });

          it('absenceTypes are empty', function () {

          });

          it('statusCount is reset', function () {

          });
        });

        describe('when data is loaded', function () {

          it('loading should be hidden', function () {

          });

          it('leave requests have data', function () {

          });

          it('absencePeriods have data', function () {

          });

          it('absenceTypes have data', function () {

          });

          it('statusCount have data', function () {

          });
        });
      });

      describe('pagination', function () {
        //TODO create directive for this

        it('next button increases the page no', function () {

        });

        it('last button sets the page no the last', function () {

        });

        it('last button sets the page no the last', function () {

        });
      });

      describe('status type', function () {

        it('sets active status type', function () {

        });
      });

      describe('filters', function () {

        it('staff member filter is set', function () {

        });

        it('region filter is set', function () {

        });

        it('department filter is set', function () {

        });

        it('level type filter is set', function () {

        });

        it('location filter is set', function () {

        });

        it('pending requests filter is set', function () {

        });
      });

      function compileComponent() {
        var $scope = $rootScope.$new();
        var contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<manager-leave-requests contact-id="' + contactId + '"></manager-leave-requests>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('managerLeaveReport');
      }
    });
  })
})(CRM);
