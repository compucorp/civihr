/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  LeaveTypeWizardFormController.$inject = ['$log', 'shared-settings'];

  return {
    __name: 'leaveTypeWizardForm',
    controller: LeaveTypeWizardFormController,
    controllerAs: 'form',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'leave-type-wizard/form/components/leave-type-wizard-form.html';
    }]
  };

  function LeaveTypeWizardFormController ($log, sharedSettings) {
    $log.debug('Controller: LeaveTypeWizardFormController');

    var vm = this;

    vm.sections = [
      {
        name: 'general',
        title: 'Leave Category'
      },
      {
        name: 'settings',
        title: 'Leave Category Settings'
      }
    ];
    vm.sectionsTemplatesPath =
      sharedSettings.sourcePath + 'leave-type-wizard/form/components/form-sections';
    vm.settingsTabs = [
      {
        name: 'basic-details',
        title: 'Basic',
        fields: [
          {
            name: 'hide_label',
            title: 'Hide leave type label on public calendars and feeds?'
          }
        ]
      },
      {
        name: 'leave-requests',
        title: 'Leave Requests',
        fields: [
          {
            name: 'max_consecutive_leave_days',
            title: 'Max consecutive duration (Leave blank for unlimited)'
          }
        ]
      },
      {
        name: 'public-holidays',
        title: 'Public Holidays',
        fields: [
          {
            name: 'must_take_public_holiday_as_leave',
            title: 'Do staff work on public holidays?'
          }
        ]
      },
      {
        name: 'carry-forwards',
        title: 'Carry Forwards',
        fields: [
          {
            name: 'allow_carry_forward',
            title: 'Allow carry forward?'
          }
        ]
      }
    ];

    vm.$onInit = $onInit;
    vm.openSection = openSection;
    vm.openSettingsTab = openSettingsTab;

    function $onInit () {
      initSections();
    }

    /**
     * Finds a sections by its name
     *
     * @param  {String} sectionName
     * @return {Object}
     */
    function findSection (sectionName) {
      return _.find(vm.sections, { name: sectionName });
    }

    /**
     * Finds a settings tab by its name
     *
     * @param  {String} settingsTabName
     * @return {Object}
     */
    function findSettingsTab (settingsTabName) {
      return _.find(vm.settingsTabs, { name: settingsTabName });
    }

    /**
     * Initiates sections.
     * Expands the General section and leaves the Settings section collapsed.
     * Selects Basic Details settings tab.
     */
    function initSections () {
      openSection('general');
      openSettingsTab('basic-details');
    }

    /**
     * Opens a section by its name and collapses all other sections
     *
     * @param {String} sectionName
     */
    function openSection (sectionName) {
      vm.sections.forEach(function (section) {
        section.expanded = false;
      });

      findSection(sectionName).expanded = true;
    }

    /**
     * Opens a settings tab by its name and collapses all other settings tabs
     *
     * @param {String} settingsTabName
     */
    function openSettingsTab (settingsTabName) {
      vm.settingsTabs.forEach(function (settingsTab) {
        settingsTab.opened = false;
      });

      findSettingsTab(settingsTabName).opened = true;
    }
  }
});
