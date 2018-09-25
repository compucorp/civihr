/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  LeaveTypeWizardFormController.$inject = ['$log', 'shared-settings',
    'form-settings-tabs', 'form-sections', 'form-leave-type-categories'];

  return {
    __name: 'leaveTypeWizardForm',
    controller: LeaveTypeWizardFormController,
    controllerAs: 'form',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'leave-type-wizard/form/components/leave-type-wizard-form.html';
    }]
  };

  function LeaveTypeWizardFormController ($log, sharedSettings,
    formSettingsTabs, formSections, formLeaveTypeCategories) {
    $log.debug('Controller: LeaveTypeWizardFormController');

    var vm = this;

    vm.leaveTypeCategory = '';
    vm.leaveTypeCategories = formLeaveTypeCategories;
    vm.activeSettingsTabIndex = null;
    vm.sections = formSections;
    vm.sectionsTemplatesPath =
      sharedSettings.sourcePath + 'leave-type-wizard/form/components/form-sections';
    vm.settingsTabs = formSettingsTabs;

    vm.$onInit = $onInit;
    vm.getFieldsForActiveSettingsTab = getFieldsForActiveSettingsTab;
    vm.openNextSettingsTab = openNextSettingsTab;
    vm.openPreviousSettingsTab = openPreviousSettingsTab;
    vm.openSection = openSection;
    vm.openSettingsTab = openSettingsTab;
    vm.selectLeaveTypeCategory = selectLeaveTypeCategory;

    function $onInit () {
      initDefaultView();
    }

    /**
     * Gets fields for the currently active settings tab
     *
     * @return {Array} collection of fields
     */
    function getFieldsForActiveSettingsTab () {
      var activeTab = _.find(vm.settingsTabs, { active: true });

      return activeTab.fields;
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
     * Opens the next settings tab
     */
    function openNextSettingsTab () {
      vm.openSettingsTab(vm.settingsTabs[vm.activeSettingsTabIndex + 1].name);
    }

    /**
     * Opens the previous settings tab
     */
    function openPreviousSettingsTab () {
      vm.openSettingsTab(vm.settingsTabs[vm.activeSettingsTabIndex - 1].name);
    }

    /**
     * Opens a section by its name and collapses all other sections
     *
     * @param {String} sectionName
     */
    function openSection (sectionName) {
      vm.sections.forEach(function (section) {
        section.active = false;
      });

      _.find(vm.sections, { name: sectionName }).active = true;
    }

    /**
     * Opens a settings tab by its name and collapses all other settings tabs
     *
     * @param {String} settingsTabName
     */
    function openSettingsTab (settingsTabName) {
      vm.settingsTabs.forEach(function (settingsTab) {
        settingsTab.active = false;
      });

      vm.activeSettingsTabIndex = _.findIndex(vm.settingsTabs, { name: settingsTabName });
      vm.activeSettingsTab = vm.settingsTabs[vm.activeSettingsTabIndex];
      vm.activeSettingsTab.active = true;
      vm.isOnLastSettingsTab = vm.activeSettingsTabIndex === vm.settingsTabs.length - 1;
      vm.isOnFirstSettingsTab = vm.activeSettingsTabIndex === 0;
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
