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
        OptionGroup, dialog, WorkPattern;

      beforeEach(module('leave-absences.templates', 'leave-absences.mocks', 'absence-tab', function (_$provide_) {
        $provide = _$provide_;
      }));

      beforeEach(inject(function (WorkPatternAPIMock) {
        $provide.value('WorkPatternAPI', WorkPatternAPIMock);
      }));
      beforeEach(inject(function (_$compile_, _$log_, _$q_, _$rootScope_, _dialog_, _OptionGroup_, _WorkPattern_) {
        $compile = _$compile_;
        $log = _$log_;
        $q = _$q_;
        $rootScope = _$rootScope_;
        OptionGroup = _OptionGroup_;
        dialog = _dialog_;

        WorkPattern = _WorkPattern_;

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
        describe('when custom Work patterns are present', function () {
          it('Loads custom Work patterns', function () {
            expect(controller.customWorkpattern.length).toEqual(workPatternData.workPatternsOf.values.length);
          });

          it('Assign correct change reason label', function () {
            _.each(controller.customWorkpattern, function (customWorkpattern) {
              var changeReasonLabel = optionGroupMock.getCollection('hrjc_revision_change_reason').find(function (reason) {
                return customWorkpattern.change_reason === reason.value;
              }).label;

              expect(customWorkpattern.change_reason_label).toBe(changeReasonLabel);
            });
          });
        });

        describe('when custom Work patterns are not present', function () {
          var defaultWorkPattern = workPatternData.getAllWorkPattern.values[0];

          beforeEach(function () {
            spyOn(WorkPattern, 'workPatternsOf').and.returnValue($q.resolve([]));
            spyOn(WorkPattern, 'default').and.returnValue($q.resolve(defaultWorkPattern));
            compileComponent();
          });

          it('Loads default Work pattern', function () {
            expect(controller.defaultWorkPattern).toEqual(defaultWorkPattern);
          });
        });

        it('sets link to work pattern listing page', function () {
          expect(controller.linkToWorkPatternListingPage).toBe('/index.php?q=civicrm/admin/leaveandabsences/' +
            'work_patterns&cid=' + contactId + '&returnUrl=%2Findex.php%3Fq%3Dcivicrm%2Fcontact%2Fview%26cid%3D202%26' +
            'selectedChild%3Dabsence');
        });
      });

      describe('deleteWorkPattern()', function () {
        var confirmFunction;
        var contactWorkPatternID = '2';

        beforeEach(function () {
          spyOn(dialog, 'open').and.callFake(function (params) {
            confirmFunction = params.onConfirm;
          });
          controller.deleteWorkPattern(contactWorkPatternID);
        });

        it('opens dialog box to confirm deletion', function () {
          expect(dialog.open).toHaveBeenCalledWith(jasmine.objectContaining({
            title: 'Confirm Cancellation?',
            copyCancel: 'Cancel',
            copyConfirm: 'Confirm',
            classConfirm: 'btn-danger',
            msg: 'This cannot be undone',
            onConfirm: jasmine.any(Function)
          }));
        });

        describe('when deletion is confirmed', function () {
          beforeEach(function () {
            spyOn(WorkPattern, 'unassignWorkPattern').and.returnValue($q.resolve([]));
            confirmFunction();
          });

          it('Contact work pattern is un assigned', function () {
            expect(WorkPattern.unassignWorkPattern).toHaveBeenCalledWith(contactWorkPatternID);
          });
        });
      });

      function compileComponent () {
        var $scope = $rootScope.$new();
        contactId = CRM.vars.leaveAndAbsences.contactId;

        component = angular.element('<absence-tab-work-patterns contact-id="' + contactId + '"></absence-tab-work-patterns>');
        $compile(component)($scope);
        $scope.$digest();

        controller = component.controller('absenceTabWorkPatterns');
      }
    });
  });
})(CRM);
