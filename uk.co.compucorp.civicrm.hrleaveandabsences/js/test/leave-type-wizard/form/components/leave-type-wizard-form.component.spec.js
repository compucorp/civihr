/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'leave-absences/leave-type-wizard/form/form.module'
], function (angular, _) {
  'use strict';

  describe('LeaveTypeWizardForm', function () {
    var $componentController, $log, controller;

    beforeEach(angular.mock.module('leave-type-wizard.form'));

    beforeEach(inject(function (_$componentController_, _$log_) {
      $componentController = _$componentController_;
      $log = _$log_;
    }));

    beforeEach(function () {
      spyOn($log, 'debug').and.callThrough();
    });

    beforeEach(function () {
      initComponent();
    });

    it('loads the form controller', function () {
      expect($log.debug).toHaveBeenCalledWith('Controller: LeaveTypeWizardFormController');
    });

    describe('on init', function () {
      var settingsSection, settingsSectionFirstTab;

      beforeEach(function () {
        settingsSection = controller.sections[1];
        settingsSectionFirstTab = _.first(settingsSection.tabs);

        controller.$onInit();
      });

      it('exports the absolute path to the components folder', function () {
        expect(/leave-type-wizard\/form\/components$/.test(
          controller.componentsPath)).toBe(true);
      });

      it('has the General section active', function () {
        expect(controller.sections[0].active).toBe(true);
      });

      it('has the Settings section collapsed', function () {
        expect(settingsSection.active).toBe(false);
      });

      it('has Settings sections tabs defined', function () {
        expect(settingsSection.tabs.length).toBe(4);
        expect(_.sample(settingsSection.tabs)).toEqual(jasmine.objectContaining({
          label: jasmine.any(String)
        }));
      });

      it('has fields defined in Settings sections tabs', function () {
        expect(_.sample(_.sample(settingsSection.tabs).fields)).toEqual(jasmine.objectContaining({
          name: jasmine.any(String),
          label: jasmine.any(String)
        }));
      });

      it('has leave type categories defined', function () {
        expect(controller.leaveTypeCategories.length).toBe(1);
        expect(_.sample(controller.leaveTypeCategories)).toEqual(jasmine.objectContaining({
          value: jasmine.any(String),
          label: jasmine.any(String),
          icon: jasmine.any(String)
        }));
      });

      it('has the Leave leave type category selected', function () {
        expect(controller.leaveTypeCategory).toBe('leave');
      });

      describe('when user clicks the Settings section header', function () {
        beforeEach(function () {
          controller.openSection(1);
        });

        it('collapses the first section', function () {
          expect(controller.sections[0].active).toBe(false);
        });

        it('expands the second section ', function () {
          expect(settingsSection.active).toBe(true);
        });

        it('renders tabs for the active section', function () {
          expect(controller.getTabsForActiveSection()).toEqual(settingsSection.tabs);
        });

        it('has the first Settings section tab selected', function () {
          expect(settingsSectionFirstTab.active).toBe(true);
        });

        it('tells that the user is on the first settings tab', function () {
          expect(controller.isOnSectionFirstTab).toEqual(true);
        });

        it('renders the fields for the active tab', function () {
          expect(_.first(controller.getFieldsForActiveTab()).name).toBe('hide_label');
        });

        describe('when user selects the middle settings tab', function () {
          beforeEach(function () {
            controller.openActiveSectionTab(1);
          });

          it('collapses the first settings tab', function () {
            expect(settingsSectionFirstTab.active).toBe(false);
          });

          it('expands the middle settings tab', function () {
            expect(settingsSection.tabs[1].active).toBe(true);
          });

          it('renders the fields related to the Leave Requests settings tab', function () {
            expect(_.first(controller.getFieldsForActiveTab()).name).toBe('max_consecutive_leave_days');
          });

          it('tells that the user is neither on the first settings tab nor on the last one', function () {
            expect(controller.isOnSectionLastTab).toEqual(false);
            expect(controller.isOnSectionFirstTab).toEqual(false);
          });
        });

        describe('when opens the next section tab', function () {
          beforeEach(function () {
            controller.openNextActiveSectionTab();
          });

          it('opens the second tab', function () {
            expect(settingsSection.tabs[1].active).toEqual(true);
          });

          describe('when opens the previous section tab', function () {
            beforeEach(function () {
              controller.openPreviousActiveSectionTab();
            });

            it('opens the first tab', function () {
              expect(settingsSection.tabs[0].active).toEqual(true);
            });
          });
        });

        describe('when user selects the last settings tab', function () {
          beforeEach(function () {
            controller.openActiveSectionTab(settingsSection.tabs.length - 1);
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
      controller = $componentController('leaveTypeWizardForm');
    }
  });
});
