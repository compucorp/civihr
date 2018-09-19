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
      var generalSection, settingsSection, basicSettingsTab, leaveRequestsSettingsTab;

      beforeEach(function () {
        generalSection = _.find(controller.sections, { name: 'general' });
        settingsSection = _.find(controller.sections, { name: 'settings' });
        basicSettingsTab = _.find(controller.settingsTabs, { name: 'basic-details' });
        leaveRequestsSettingsTab = _.find(controller.settingsTabs, { name: 'leave-requests' });

        controller.$onInit();
      });

      it('exports the absolute path to the form sections templates folder', function () {
        expect(/leave-type-wizard\/form\/components\/form-sections$/.test(
          controller.sectionsTemplatesPath)).toBe(true);
      });

      it('has the General section expanded', function () {
        expect(generalSection.expanded).toBe(true);
      });

      it('has the Settings section collapsed', function () {
        expect(settingsSection.expanded).toBe(false);
      });

      it('has Settings sections tabs defined', function () {
        expect(controller.settingsTabs.length).toBe(4);
        expect(_.sample(controller.settingsTabs)).toEqual(jasmine.objectContaining({
          name: jasmine.any(String),
          title: jasmine.any(String)
        }));
      });

      it('has the Basic settings tab selected', function () {
        expect(basicSettingsTab.opened).toBe(true);
      });

      describe('when user clicks the Settings section header', function () {
        beforeEach(function () {
          controller.openSection('settings');
        });

        it('collapses the General section', function () {
          expect(generalSection.expanded).toBe(false);
        });

        it('expands the Settings section ', function () {
          expect(settingsSection.expanded).toBe(true);
        });

        describe('when user selects the Leave Requests settings tab', function () {
          beforeEach(function () {
            controller.openSettingsTab('leave-requests');
          });

          it('collapses the Basic Details settings tab', function () {
            expect(basicSettingsTab.opened).toBe(false);
          });

          it('expands the Leave Requests settings tab', function () {
            expect(leaveRequestsSettingsTab.opened).toBe(true);
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
