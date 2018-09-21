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

    vm.leaveTypeCategory = '';
    vm.leaveTypeCategories = [
      {
        value: 'leave',
        label: 'Leave',
        icon: 'plane'
      }
    ];
    vm.leaveTypeTitle = '';
    vm.openedSettingsTabIndex = null;
    vm.sections = [
      {
        name: 'general',
        label: 'Leave Category'
      },
      {
        name: 'settings',
        label: 'Leave Category Settings'
      }
    ];
    vm.sectionsTemplatesPath =
      sharedSettings.sourcePath + 'leave-type-wizard/form/components/form-sections';
    vm.settingsTabs = [
      {
        name: 'basic-details',
        label: 'Basic',
        fields: [
          {
            name: 'hide_label',
            label: 'Hide leave type label on public calendars and feeds?'
          }
        ]
      },
      {
        name: 'leave-requests',
        label: 'Leave Requests',
        fields: [
          {
            name: 'max_consecutive_leave_days',
            label: 'Max consecutive duration (Leave blank for unlimited)'
          }
        ]
      },
      {
        name: 'public-holidays',
        label: 'Public Holidays',
        fields: [
          {
            name: 'must_take_public_holiday_as_leave',
            label: 'Do staff work on public holidays?'
          }
        ]
      },
      {
        name: 'carry-forwards',
        label: 'Carry Forwards',
        fields: [
          {
            name: 'allow_carry_forward',
            label: 'Allow carry forward?'
          }
        ]
      }
    ];

    vm.$onInit = $onInit;
    vm.getSettingsTabFields = getSettingsTabFields;
    vm.openSection = openSection;
    vm.openSettingsTab = openSettingsTab;
    vm.selectLeaveTypeCategory = selectLeaveTypeCategory;

    function $onInit () {
      initDefaultView();
    }

    /**
     * Gets fields for the currently opened settings tab
     *
     * @return {Array} collection of fields
     */
    function getSettingsTabFields () {
      var openedTab = _.find(vm.settingsTabs, { opened: true });

      return openedTab.fields;
    }

    /**
     * Initiates the default view:
     * - selects Leave category;
     * - expands the General section and leaves the Settings section collapsed;
     * - selects Basic Details settings tab.
     */
    function initDefaultView () {
      vm.leaveTypeCategory = 'leave';

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

      _.find(vm.sections, { name: sectionName }).expanded = true;
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

      vm.openedSettingsTabIndex = _.findIndex(vm.settingsTabs, { name: settingsTabName });
      vm.settingsTabs[vm.openedSettingsTabIndex].opened = true;
    }

    /**
     * Selects a leave type category.
     *
     * @param {String} value
     */
    function selectLeaveTypeCategory (value) {
      vm.leaveTypeCategories.forEach(function (category) {
        category.selected = false;
      });

      _.find(vm.leaveTypeCategories, { value: value }).selected = true;
    }
  }
});
