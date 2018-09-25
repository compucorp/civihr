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
      var sections, firstSettingsTab, middleSettingsTab, lastSettingsTab;

      beforeEach(function () {
        sections = _.keyBy(controller.sections, 'name');
        firstSettingsTab = _.first(controller.settingsTabs);
        middleSettingsTab = controller.settingsTabs[1];
        lastSettingsTab = _.last(controller.settingsTabs);

        controller.$onInit();
      });

      it('exports the absolute path to the form sections templates folder', function () {
        expect(/leave-type-wizard\/form\/components\/form-sections$/.test(
          controller.sectionsTemplatesPath)).toBe(true);
      });

      it('has the General section active', function () {
        expect(sections.general.active).toBe(true);
      });

      it('has the Settings section collapsed', function () {
        expect(sections.settings.active).toBe(false);
      });

      it('has Settings sections tabs defined', function () {
        expect(controller.settingsTabs.length).toBe(4);
        expect(_.sample(controller.settingsTabs)).toEqual(jasmine.objectContaining({
          name: jasmine.any(String),
          label: jasmine.any(String)
        }));
      });

      it('has fields defined in Settings sections tabs', function () {
        expect(controller.settingsTabs.length).toBe(4);
        expect(_.sample(_.sample(controller.settingsTabs).fields)).toEqual(jasmine.objectContaining({
          name: jasmine.any(String),
          label: jasmine.any(String)
        }));
      });

      it('has the first settings tab selected', function () {
        expect(firstSettingsTab.active).toBe(true);
        expect(controller.activeSettingsTab).toBe(firstSettingsTab);
      });

      it('tells that the user is on the first settings tab', function () {
        expect(controller.isOnFirstSettingsTab).toEqual(true);
      });

      it('stores an index of the active settings tab', function () {
        expect(controller.activeSettingsTabIndex).toEqual(0);
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
          controller.openSection('settings');
        });

        it('collapses the General section', function () {
          expect(sections.general.active).toBe(false);
        });

        it('expands the Settings section ', function () {
          expect(sections.settings.active).toBe(true);
        });

        it('renders the fields related to the Basic Details settings tab', function () {
          expect(_.first(controller.getFieldsForActiveSettingsTab()).name).toBe('hide_label');
        });

        describe('when user selects the middle settings tab', function () {
          beforeEach(function () {
            controller.openSettingsTab(middleSettingsTab.name);
          });

          it('collapses the first settings tab', function () {
            expect(firstSettingsTab.active).toBe(false);
          });

          it('expands the middle settings tab', function () {
            expect(controller.activeSettingsTab).toEqual(middleSettingsTab);
            expect(middleSettingsTab.active).toBe(true);
          });

          it('renders the fields related to the Leave Requests settings tab', function () {
            expect(_.first(controller.getFieldsForActiveSettingsTab()).name).toBe('max_consecutive_leave_days');
          });

          it('updates the reference to the active settings tab', function () {
            expect(controller.activeSettingsTab).toEqual(middleSettingsTab);
          });

          it('tells that the user is neither on the first settings tab nor on the last one', function () {
            expect(controller.isOnLastSettingsTab).toEqual(false);
            expect(controller.isOnFirstSettingsTab).toEqual(false);
          });

          it('updates the index of the active settings tab', function () {
            expect(controller.activeSettingsTabIndex).toEqual(1);
          });
        });

        describe('when opens the next section', function () {
          beforeEach(function () {
            controller.openNextSettingsTab();
          });

          it('updates the index of the active settings tab', function () {
            expect(controller.activeSettingsTabIndex).toEqual(1);
          });

          describe('when opens the previous section', function () {
            beforeEach(function () {
              controller.openPreviousSettingsTab();
            });

            it('updates the index of the active settings tab', function () {
              expect(controller.activeSettingsTabIndex).toEqual(0);
            });
          });
        });

        describe('when user selects the last settings tab', function () {
          beforeEach(function () {
            controller.openSettingsTab(lastSettingsTab.name);
          });

          it('tells that the user is on the last settings tab', function () {
            expect(controller.isOnLastSettingsTab).toEqual(true);
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
