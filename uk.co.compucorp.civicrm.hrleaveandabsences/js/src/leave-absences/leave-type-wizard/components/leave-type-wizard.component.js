/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  LeaveTypeWizardController.$inject = ['$log', '$scope', 'AbsenceType', 'Contact',
    'form-sections', 'shared-settings'];

  return {
    leaveTypeWizard: {
      controller: LeaveTypeWizardController,
      controllerAs: 'form',
      templateUrl: ['shared-settings', function (sharedSettings) {
        return sharedSettings.sourcePath + 'leave-type-wizard/components/leave-type-wizard.html';
      }]
    }
  };

  function LeaveTypeWizardController ($log, $scope, AbsenceType, Contact,
    formSections, sharedSettings) {
    $log.debug('Controller: LeaveTypeWizardController');

    var vm = this;

    vm.availableColours = [];
    vm.componentsPath =
      sharedSettings.sourcePath + 'leave-type-wizard/components';
    vm.fieldsIndexed = {};
    vm.leaveTypeCategories = [
      {
        value: 'leave',
        label: 'Leave',
        icon: 'plane'
      }
    ];
    vm.sections = formSections;

    vm.$onInit = $onInit;
    vm.nextTabHandler = nextTabHandler;
    vm.previousTabHandler = previousTabHandler;
    vm.openSection = openSection;
    vm.openActiveSectionTab = openActiveSectionTab;

    function $onInit () {
      initDefaultView();
      indexFields();
      initDefaultValues();
      initValidators();
      initFieldsWatchers();
      loadContacts();
      loadAvailableColours();
    }

    function findIndexesOfFirstSectionAndTabWithErrors () {
      var indexes;

      _.each(vm.sections, function (section, sectionIndex) {
        _.each(section.tabs, function (tab, tabIndex) {
          if (!tab.valid) {
            indexes = {
              sectionIndex: sectionIndex,
              tabIndex: tabIndex
            };

            return false;
          }
        });

        if (indexes) {
          return false;
        }
      });

      return indexes;
    }

    /**
     * Returns the active tab
     *
     * @return {Object}
     */
    function getActiveTab () {
      var activeSection = getActiveSection();

      return _.find(activeSection.tabs, { active: true });
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
     * Indexes fields for quicker access and sets them to the component
     */
    function indexFields () {
      vm.fieldsIndexed = _.chain(vm.sections)
        .flatMap('tabs')
        .flatMap('fields')
        .keyBy('name')
        .value();
    }

    /**
     * Initiates default values for all fields.
     * Skips the field if the current value is defined or the default value is not defined.
     */
    function initDefaultValues () {
      _.each(vm.fieldsIndexed, function (field) {
        if (field.value !== undefined || field.defaultValue === undefined) {
          return;
        }

        field.value = field.defaultValue;
      });
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
     * Initiates all fields watchers
     */
    function initFieldsWatchers () {
      watchFieldAllowCarryForward();
    }

    /**
     * Initiates validators of a given field
     *
     * @param {Object} field
     */
    function initValidatorsForField (field) {
      $scope.$watch(function () {
        return field.value;
      }, function (value, oldValue) {
        var activeTab;

        if (value === oldValue) {
          return;
        }

        activeTab = getActiveTab();

        delete field.error;

        validateField(field);

        if (activeTab.valid !== undefined) {
          validateTab(activeTab);
        }
      });
    }

    /**
     * Initiates validators for all fields
     */
    function initValidators () {
      _.each(vm.fieldsIndexed, function (field) {
        if (!field.validations && !field.required) {
          return;
        }

        initValidatorsForField(field);
      });
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
     * Fetches contact and sets them to the component
     *
     * @return {Promise}
     */
    function loadContacts () {
      return Contact.all()
        .then(function (contacts) {
          vm.contacts = contacts.list;
        });
    }

    /**
     * Opens the next tab in the active section
     */
    function nextTabHandler () {
      var activeTabIndex = getActiveTabIndex();
      var activeTab = getActiveTab();

      validateAllTabFields(activeTab);
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
      var sectionToOpen = vm.sections[sectionIndex];

      vm.sections.forEach(function (section) {
        section.active = false;
      });

      if (!sectionToOpen) {
        save();

        return;
      }

      sectionToOpen.active = true;

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
      var nextTab = tabs[tabIndex];

      tabs.forEach(function (tab) {
        tab.active = false;
      });

      if (!nextTab) {
        if (tabIndex === -1) {
          openPreviousSection();
        } else {
          openNextSection();
        }

        return;
      }

      nextTab.active = true;
      vm.isOnSectionLastTab = tabIndex === tabs.length - 1;
      vm.isOnSectionFirstTab = tabIndex === 0;
    }

    /**
     * Opens the previous tab in the active section
     */
    function previousTabHandler () {
      var activeTabIndex = getActiveTabIndex();

      openActiveSectionTab(activeTabIndex - 1);
    }

    function save () {
      var sectionAndTabWithErrorsIndexes;

      validateAllSections();

      sectionAndTabWithErrorsIndexes = findIndexesOfFirstSectionAndTabWithErrors();

      if (sectionAndTabWithErrorsIndexes) {
        openSection(sectionAndTabWithErrorsIndexes.sectionIndex);
        openActiveSectionTab(sectionAndTabWithErrorsIndexes.tabIndex);
      }
    }

    /**
     * Validates a field
     *
     * @param {Object} field
     */
    function validateField (field) {
      if (field.required && field.value === '') {
        field.error = 'This field is required';
      } else if (field.value !== '' && field.validations) {
        field.validations.forEach(function (validation) {
          if (!validation.rule.test(field.value)) {
            field.error = validation.message;
          }
        });
      }
    }

    function validateAllSections () {
      vm.sections.forEach(function (section) {
        section.tabs.forEach(function (tab) {
          validateAllTabFields(tab);
        });
      });
    }

    /**
     * Validates all fields for a tab
     * and checks if the whole tab is filled in correctly
     *
     * @param {Object} tab
     */
    function validateAllTabFields (tab) {
      tab.fields.forEach(function (field) {
        validateField(field);
      });
      validateTab(tab);
    }

    /**
     * Validates a whole tab.
     * It runs through fields and checks if the tab fields contain errors.
     *
     * @param {Object} tab
     */
    function validateTab (tab) {
      var tabIsValid = !_.find(tab.fields, function (field) {
        return field.error;
      });

      tab.valid = tabIsValid;
    }

    /**
     * Initiates a watcher over the "Allow carry forward" field.
     * Toggles dependent fields on value change.
     */
    function watchFieldAllowCarryForward () {
      $scope.$watch(function () {
        return vm.fieldsIndexed.allow_carry_forward.value;
      }, function (allowCarryForward) {
        vm.fieldsIndexed.max_number_of_days_to_carry_forward.hidden = !allowCarryForward;
        vm.fieldsIndexed.carry_forward_expiration_duration.hidden = !allowCarryForward;
      });
    }
  }
});
