/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  LeaveTypeWizardController.$inject = ['$log', 'shared-settings',
    'form-sections', 'form-leave-type-categories'];

  return {
    __name: 'leaveTypeWizard',
    controller: LeaveTypeWizardController,
    controllerAs: 'form',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'leave-type-wizard/components/leave-type-wizard.html';
    }]
  };

  function LeaveTypeWizardController ($log, sharedSettings,
    formSections, formLeaveTypeCategories) {
    $log.debug('Controller: LeaveTypeWizardFormController');

    var vm = this;

    vm.componentsPath =
      sharedSettings.sourcePath + 'leave-type-wizard/components';
    vm.leaveTypeCategory = '';
    vm.leaveTypeCategories = formLeaveTypeCategories;
    vm.sections = formSections;

    vm.$onInit = $onInit;
    vm.getFieldsForActiveTab = getFieldsForActiveTab;
    vm.getTabsForActiveSection = getTabsForActiveSection;
    vm.openNextActiveSectionTab = openNextActiveSectionTab;
    vm.openPreviousActiveSectionTab = openPreviousActiveSectionTab;
    vm.openSection = openSection;
    vm.openActiveSectionTab = openActiveSectionTab;
    vm.selectLeaveTypeCategory = selectLeaveTypeCategory;

    function $onInit () {
      initDefaultView();
    }

    /**
     * Gets fields for the active tab of the active section
     *
     * @return {Array} collection of fields
     */
    function getFieldsForActiveTab () {
      var activeSectionTabs = vm.getTabsForActiveSection();
      var activeTab = _.find(activeSectionTabs, { active: true });

      return activeTab.fields;
    }

    /**
     * Gets tabs for the active section
     *
     * @return {Array} collection of tabs
     */
    function getTabsForActiveSection () {
      return _.find(vm.sections, { active: true }).tabs;
    }

    /**
     * Initiates the default view:
     * - selects Leave category;
     * - expands the General section and leaves the Settings section collapsed;
     * - selects Basic Details settings tab.
     */
    function initDefaultView () {
      vm.leaveTypeCategory = 'leave';

      openSection(0);
    }

    /**
     * Opens the next tab in the active section
     */
    function openNextActiveSectionTab () {
      var activeSection = _.find(vm.sections, { active: true });
      var activeTabIndex = _.findIndex(activeSection.tabs, { active: true });

      vm.openActiveSectionTab(activeTabIndex + 1);
    }

    /**
     * Opens the previous tab in the active section
     */
    function openPreviousActiveSectionTab () {
      var activeSection = _.find(vm.sections, { active: true });
      var activeTabIndex = _.findIndex(activeSection.tabs, { active: true });

      vm.openActiveSectionTab(activeTabIndex - 1);
    }

    /**
     * Opens a section by its index, collapses all other sections and,
     * if there are any tabs, opens the first tab.
     *
     * @param {Number} sectionIndex
     */
    function openSection (sectionIndex) {
      var section = vm.sections[sectionIndex];

      vm.sections.forEach(function (section) {
        section.active = false;
      });

      section.active = true;

      section.tabs && openActiveSectionTab(0);
    }

    /**
     * Opens a section tab by its index and collapses all other section tabs
     *
     * @param {Number} tabIndex
     */
    function openActiveSectionTab (tabIndex) {
      var activeSection = _.find(vm.sections, { active: true });
      var tabs = activeSection.tabs;

      tabs.forEach(function (tab) {
        tab.active = false;
      });

      tabs[tabIndex].active = true;
      vm.isOnSectionLastTab = tabIndex === tabs.length - 1;
      vm.isOnSectionFirstTab = tabIndex === 0;
    }

    /**
     * Selects a leave type category
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
