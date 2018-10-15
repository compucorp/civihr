/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  LeaveTypeWizardController.$inject = ['$log', 'AbsenceType', 'shared-settings',
    'form-sections'];

  return {
    leaveTypeWizard: {
      controller: LeaveTypeWizardController,
      controllerAs: 'form',
      templateUrl: ['shared-settings', function (sharedSettings) {
        return sharedSettings.sourcePath + 'leave-type-wizard/components/leave-type-wizard.html';
      }]
    }
  };

  function LeaveTypeWizardController ($log, AbsenceType, sharedSettings,
    formSections) {
    $log.debug('Controller: LeaveTypeWizardController');

    var vm = this;

    vm.availableColours = [];
    vm.componentsPath =
      sharedSettings.sourcePath + 'leave-type-wizard/components';
    vm.leaveTypeCategories = [
      {
        value: 'leave',
        label: 'Leave',
        icon: 'plane'
      }
    ];
    vm.sections = formSections;

    vm.$onInit = $onInit;
    vm.getFieldsForActiveTab = getFieldsForActiveTab;
    vm.openNextActiveSectionTab = openNextActiveSectionTab;
    vm.openPreviousActiveSectionTab = openPreviousActiveSectionTab;
    vm.openNextSection = openNextSection;
    vm.openPreviousSection = openPreviousSection;
    vm.openSection = openSection;
    vm.openActiveSectionTab = openActiveSectionTab;

    function $onInit () {
      initDefaultView();
      loadAvailableColours();
    }

    /**
     * Returns the index of the active tab
     *
     * @return {Number}
     */
    function getActiveTabIndex () {
      var activeSection = getActiveSection();

      return _.findIndex(activeSection.tabs, { active: true });
    }

    /**
     * Returns the active section
     *
     * @return {Object}
     */
    function getActiveSection () {
      return _.find(vm.sections, { active: true });
    }

    /**
     * Returns the index of the active section
     *
     * @return {Number}
     */
    function getActiveSectionIndex () {
      return _.findIndex(vm.sections, { active: true });
    }

    /**
     * Gets fields for the active tab of the active section
     *
     * @return {Array} collection of fields
     */
    function getFieldsForActiveTab () {
      var activeSection = getActiveSection();
      var activeTab = _.find(activeSection.tabs, { active: true });

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

      openSection(0);
    }

    /**
     * Fetches available colours and sets them to the component
     *
     * @return {Promise}
     */
    function loadAvailableColours () {
      return AbsenceType.getAvailableColours()
        .then(function (availableColours) {
          vm.availableColours = availableColours;
        });
    }

    /**
     * Opens the next tab in the active section
     */
    function openNextActiveSectionTab () {
      var activeTabIndex = getActiveTabIndex();

      openActiveSectionTab(activeTabIndex + 1);
    }

    /**
     * Opens next section
     */
    function openNextSection (currentIndex) {
      var activeSectionIndex = getActiveSectionIndex();

      openSection(activeSectionIndex + 1);
    }

    /**
     * Opens the previous tab in the active section
     */
    function openPreviousActiveSectionTab () {
      var activeTabIndex = getActiveTabIndex();

      openActiveSectionTab(activeTabIndex - 1);
    }

    /**
     * Opens previous section
     */
    function openPreviousSection () {
      var activeSectionIndex = getActiveSectionIndex();

      openSection(activeSectionIndex - 1);
    }

    /**
     * Opens a section by its index, collapses all other sections and,
     * if there are any tabs, opens the first tab.
     *
     * @param {Number} sectionIndex
     */
    function openSection (sectionIndex) {
      vm.sections.forEach(function (section) {
        section.active = false;
      });

      vm.sections[sectionIndex].active = true;

      openActiveSectionTab(0);
    }

    /**
     * Opens a section tab by its index and collapses all other section tabs
     *
     * @param {Number} tabIndex
     */
    function openActiveSectionTab (tabIndex) {
      var activeSection = getActiveSection();
      var tabs = activeSection.tabs;

      tabs.forEach(function (tab) {
        tab.active = false;
      });

      tabs[tabIndex].active = true;
      vm.isOnSectionLastTab = tabIndex === tabs.length - 1;
      vm.isOnSectionFirstTab = tabIndex === 0;
    }
  }
});
