/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/lodash',
    'mocks/data/option-group-mock-data',
    'mocks/data/work-pattern-data',
    'mocks/apis/work-pattern-api-mock',
    'leave-absences/absence-tab/app'
  ], function (angular, _, optionGroupMock, workPatternData) {
    'use strict';

    describe('absenceTabWorkPatterns', function () {
      var $compile, $log, $q, $rootScope, $provide, component, controller, contactId,
        OptionGroup, WorkPatternAPI;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'absence-tab', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (WorkPatternAPIMock) {
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
      }));
      beforeEach(inject(function (_$compile_, _$log_, _$q_, _$rootScope_, _OptionGroup_, _WorkPatternAPI_) {
        $compile = _$compile_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroup = _OptionGroup_;

        WorkPatternAPI = _WorkPatternAPI_;

        spyOn(OptionGroup, 'valuesOf').and.callFake(function () {
          return $q.resolve(optionGroupMock.getCollection('hrjc_revision_change_reason'));
        });
        spyOn($log, 'debug');

        compileComponent();
      }));

      it('is initialized', function () {
        expect($log.debug).toHaveBeenCalled();
      });

      describe('init()', function () {
        it('loads change reasons', function () {
          expect(controller.changeReasons).toEqual(optionGroupMock.getCollection('hrjc_revision_change_reason'));
        });

        it('loads work patterns', function () {
          expect(controller.workPatterns).toEqual(workPatternData.getAllWorkPattern.values);
        });
      });

      describe('closeModal()', function () {
        beforeEach(function () {
          spyOn(controller, 'dismiss');
          controller.closeModal();
        });

        it('closes the modal', function () {
          expect(controller.dismiss).toHaveBeenCalledWith({ $value: 'cancel' });
        });
      });

      describe('closeAlert()', function () {
        beforeEach(function () {
          controller.closeAlert();
        });

        it('error message is reset', function () {
          expect(controller.errorMessage).toBe('');
        });
      });

      describe('save()', function () {
        var promise;

        beforeEach(function () {
          controller.selected.workPattern = { id: '2' };
          controller.selected.changeReason = '3';
          controller.selected.effectiveDate = '2017-06-15';
          spyOn($rootScope, '$broadcast');
          spyOn(controller, 'closeModal');
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        describe('when assignment is saved successfully', function () {
          beforeEach(function () {
            spyOn(WorkPatternAPI, 'assignWorkPattern').and.returnValue($q.resolve([]));
            promise = controller.save();
          });

          it('work pattern is assigned', function () {
            expect(WorkPatternAPI.assignWorkPattern).toHaveBeenCalledWith(
              controller.contactId,
              controller.selected.workPattern.id,
              controller.selected.effectiveDate,
              null,
              controller.selected.changeReason);
          });

          it('broadcasts an event', function () {
            promise.then(function () {
              expect($rootScope.$broadcast).toHaveBeenCalledWith('CustomWorkPattern::Added');
            });
          });

          it('modal is closed', function () {
            promise.then(function () {
              expect(controller.closeModal).toHaveBeenCalled();
            });
          });
        });

        describe('when assignment API returns error', function () {
          var errorMessage = 'Some error';

          beforeEach(function () {
            spyOn(WorkPatternAPI, 'assignWorkPattern').and.returnValue($q.reject(errorMessage));
            promise = controller.save();
          });

          it('error message is shown on UI', function () {
            promise.then(function () {
              expect(controller.errorMessage).toBe(errorMessage);
            });
          });
        });
      });

      function compileComponent () {
        var $scope = $rootScope.$new();
        contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<absence-tab-custom-work-pattern-modal contact-id="' + contactId + '"></absence-tab-custom-work-pattern-modal>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('absenceTabCustomWorkPatternModal');
      }
    });
  });
})(CRM);
