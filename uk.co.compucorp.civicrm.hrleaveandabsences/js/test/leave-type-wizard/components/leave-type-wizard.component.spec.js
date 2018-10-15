/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'leave-absences/leave-type-wizard/leave-type-wizard.module'
], function (angular, _) {
  'use strict';

  describe('LeaveTypeWizard', function () {
    var $componentController, $log, $q, $rootScope, AbsenceType, controller;
    var sampleAvailableColours = ['#FFFFFF', '#000000'];

    beforeEach(angular.mock.module('leave-type-wizard'));

    beforeEach(inject(function (_$componentController_, _$log_, _$q_, _$rootScope_,
      _AbsenceType_) {
      AbsenceType = _AbsenceType_;
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
    }));

    beforeEach(function () {
      spyOn(AbsenceType, 'getAvailableColours').and.returnValue($q.resolve(sampleAvailableColours));
      spyOn($log, 'debug').and.callThrough();
    });

    beforeEach(function () {
      initComponent();
    });

    it('loads the wizard controller', function () {
      expect($log.debug).toHaveBeenCalledWith('Controller: LeaveTypeWizardController');
    });

    describe('on init', function () {
      var secondSection, secondSectionFirstTab;

      beforeEach(function () {
        secondSection = controller.sections[1];
        secondSectionFirstTab = _.first(secondSection.tabs);

        controller.$onInit();
        $rootScope.$digest();
      });

      it('exports the absolute path to the components folder', function () {
        expect(/leave-type-wizard\/components$/.test(
          controller.componentsPath)).toBe(true);
      });

      it('has the General section active', function () {
        expect(controller.sections[0].active).toBe(true);
      });

      it('has the second section collapsed', function () {
        expect(secondSection.active).toBe(false);
      });

      it('has second section tabs defined', function () {
        expect(secondSection.tabs.length).toBe(4);
        expect(_.sample(secondSection.tabs)).toEqual(jasmine.objectContaining({
          label: jasmine.any(String)
        }));
      });

      it('has fields defined in second section tabs', function () {
        expect(_.sample(_.sample(secondSection.tabs).fields)).toEqual(jasmine.objectContaining({
          name: jasmine.any(String),
          label: jasmine.any(String)
        }));
      });

      it('has the Leave leave type category selected', function () {
        expect(controller.leaveTypeCategory).toBe('leave');
      });

      it('loads available colours', function () {
        expect(AbsenceType.getAvailableColours).toHaveBeenCalledWith();
        expect(controller.availableColours).toEqual(sampleAvailableColours);
      });

      describe('when user clicks the "next section" button', function () {
        beforeEach(function () {
          controller.openNextSection();
        });

        it('collapses the first section', function () {
          expect(controller.sections[0].active).toBe(false);
        });

        it('expands the second section ', function () {
          expect(secondSection.active).toBe(true);
        });

        describe('when user clicks the "next section" button', function () {
          beforeEach(function () {
            controller.openPreviousSection();
          });

          it('collapses the second section', function () {
            expect(secondSection.active).toBe(false);
          });

          it('expands the first section ', function () {
            expect(controller.sections[0].active).toBe(true);
          });
        });
      });

      describe('when user clicks the second section header', function () {
        beforeEach(function () {
          controller.openSection(1);
        });

        it('collapses the first section', function () {
          expect(controller.sections[0].active).toBe(false);
        });

        it('expands the second section ', function () {
          expect(secondSection.active).toBe(true);
        });

        it('has the first tab selected', function () {
          expect(secondSectionFirstTab.active).toBe(true);
        });

        it('tells that the user is on the first tab section', function () {
          expect(controller.isOnSectionFirstTab).toEqual(true);
        });

        it('renders the fields for the active tab', function () {
          expect(_.first(controller.getFieldsForActiveTab()).name).toBe('hide_label');
        });

        describe('when user selects the middle tab', function () {
          beforeEach(function () {
            controller.openActiveSectionTab(1);
          });

          it('collapses the first tab', function () {
            expect(secondSectionFirstTab.active).toBe(false);
          });

          it('expands the middle tab', function () {
            expect(secondSection.tabs[1].active).toBe(true);
          });

          it('renders the fields related to the second tab', function () {
            expect(_.first(controller.getFieldsForActiveTab()).name).toBe('max_consecutive_leave_days');
          });

          it('tells that the user is neither on the first tab nor on the last one', function () {
            expect(controller.isOnSectionLastTab).toEqual(false);
            expect(controller.isOnSectionFirstTab).toEqual(false);
          });
        });

        describe('when opens the next section tab', function () {
          beforeEach(function () {
            controller.openNextActiveSectionTab();
          });

          it('opens the second tab', function () {
            expect(secondSection.tabs[1].active).toEqual(true);
          });

          describe('when opens the previous section tab', function () {
            beforeEach(function () {
              controller.openPreviousActiveSectionTab();
            });

            it('opens the first tab', function () {
              expect(secondSection.tabs[0].active).toEqual(true);
            });
          });
        });

        describe('when user selects the last tab', function () {
          beforeEach(function () {
            controller.openActiveSectionTab(secondSection.tabs.length - 1);
          });

          it('tells that the user is on the last tab', function () {
            expect(controller.isOnSectionLastTab).toEqual(true);
          });
        });
      });
    });

    /**
     * Initiates the component and stores it for tests
     */
    function initComponent () {
      controller = $componentController('leaveTypeWizard');
    }
  });
});
