/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  LeaveTypeWizardController.$inject = ['$log', '$q', '$scope', '$window',
    'AbsenceType', 'Contact', 'form-sections', 'notificationService',
    'shared-settings'];

  return {
    leaveTypeWizard: {
      controller: LeaveTypeWizardController,
      controllerAs: 'form',
      templateUrl: ['shared-settings', function (sharedSettings) {
        return sharedSettings.sourcePath + 'leave-type-wizard/components/leave-type-wizard.html';
      }]
    }
  };

  function LeaveTypeWizardController ($log, $q, $scope, $window, AbsenceType,
    Contact, formSections, notificationService, sharedSettings) {
    $log.debug('Controller: LeaveTypeWizardController');

    var absenceTypes = [];
    var state = {
      sectionIndex: null,
      tabIndex: null
    };
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
    vm.loading = true;
    vm.sections = formSections;

    vm.$onInit = $onInit;
    vm.nextTabHandler = nextTabHandler;
    vm.previousTabHandler = previousTabHandler;
    vm.openSection = openSection;
    vm.openActiveSectionTab = openActiveSectionTab;

    function $onInit () {
      vm.loading = true;

      $q.all([
        loadContacts(),
        loadAvailableColours(),
        loadAbsenceTypes()
      ])
        .then(initDefaultView)
        .then(indexFields)
        .then(initDefaultValues)
        .then(initFieldsWatchers)
        .then(initValidators)
        .then(initCustomValidators)
        .finally(function () {
          vm.loading = false;
        });
    }

    /**
     * Searches for a tab with errors and,
     * if finds, returnes indexes of the section and the tab.
     *
     * @return {Object} { sectionIndex, tabIndex }
     */
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
     * Flushes an error for a given field
     *
     * @param {Object} field
     */
    function flushErrorForField (field) {
      delete field.error;
    }

    /**
     * Returns the active tab
     *
     * @return {Object}
     */
    function getActiveTab () {
      return vm.sections[state.sectionIndex].tabs[state.tabIndex];
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
     * Initiates custom validators
     */
    function initCustomValidators () {
      watchTitleField();
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
      watchAllowCarryForwardField();
      watchCarryForwardExpirySwitch();
    }

    /**
     * Initiates validators of a given field
     *
     * @param {Object} field
     */
    function initValidatorsForField (field) {
      $scope.$watch(function () {
        return field.value;
      }, function (newValue, oldValue) {
        var activeTab = getActiveTab();
        var wasActiveTabValidatedBefore = activeTab.valid !== undefined;

        if (newValue === oldValue) {
          return;
        }

        wasActiveTabValidatedBefore
          ? validateTab(activeTab)
          : validateField(field);
      });
    }

    /**
     * Initiates validators for all fields
     */
    function initValidators () {
      _.each(vm.fieldsIndexed, function (field) {
        initValidatorsForField(field);
      });
    }

    /**
     * Fetches absence types and stores them in the component.
     * Fetches only absence types' titles.
     */
    function loadAbsenceTypes () {
      return AbsenceType.all({}, { return: ['title'] })
        .then(function (_absenceTypes_) {
          absenceTypes = _absenceTypes_;
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
     * Redirects to the leave types list page
     */
    function navigateToLeaveTypesList () {
      $window.location.href = $window.location.origin +
        '/civicrm/admin/leaveandabsences/types?action=browse&reset=1';
    }

    /**
     * Opens the next tab in the active section
     */
    function nextTabHandler () {
      var activeTab = getActiveTab();

      validateTab(activeTab);
      openActiveSectionTab(state.tabIndex + 1);
    }

    /**
     * Opens next section
     */
    function openNextSection () {
      openSection(state.sectionIndex + 1);
    }

    /**
     * Opens previous section
     */
    function openPreviousSection () {
      openSection(state.sectionIndex - 1);
    }

    /**
     * Opens a section by its index, collapses all other sections and,
     * if there are any tabs, opens the first tab.
     *
     * @param {Number} sectionIndex
     */
    function openSection (sectionIndex) {
      var sectionToOpen;

      if (sectionIndex === -1) {
        navigateToLeaveTypesList();

        return;
      }

      sectionToOpen = vm.sections[sectionIndex];

      if (!sectionToOpen) {
        submit();

        return;
      }

      vm.sections.forEach(function (section) {
        section.active = false;
      });

      state.sectionIndex = sectionIndex;
      sectionToOpen.active = true;

      openActiveSectionTab(0);
    }

    /**
     * Opens a section tab by its index and collapses all other section tabs
     *
     * @param {Number} tabIndex
     */
    function openActiveSectionTab (tabIndex) {
      var activeSection = vm.sections[state.sectionIndex];
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

      state.tabIndex = tabIndex;
      nextTab.active = true;
      vm.isOnSectionLastTab = tabIndex === tabs.length - 1;
      vm.isOnSectionFirstTab = tabIndex === 0;
    }

    /**
     * Pre-processes parameters for sending them to the backend.
     * - flushes dependent fields' values
     * - deletes fields held for UX only
     *
     */
    function preProcessParams (params) {
      if (!params.allow_carry_forward) {
        params.max_number_of_days_to_carry_forward = '';
      }

      if (!params.allow_carry_forward || !params.carry_forward_expiration_duration_switch) {
        params.carry_forward_expiration_duration = '';
        params.carry_forward_expiration_unit = '';
      }

      delete params.carry_forward_expiration_duration_switch;
    }

    /**
     * Opens the previous tab in the active section
     */
    function previousTabHandler () {
      openActiveSectionTab(state.tabIndex - 1);
    }

    /**
     * Saves leave type by sending an API call to the backend
     * with all appropriate parameters.
     */
    function save () {
      var params = _.chain(vm.fieldsIndexed)
        .keyBy('name')
        .mapValues('value')
        .value();

      vm.loading = true;

      preProcessParams(params);
      AbsenceType.save(params)
        .then(navigateToLeaveTypesList)
        .catch(function (error) {
          notificationService.error('', error);
          openSection(0);

          vm.loading = false;
        });
    }

    /**
     * Submits the whole wizard.
     * Validates all fields and, if all valid, saves the form.
     * If errors are found, navigates to the first found tab with errors.
     */
    function submit () {
      var sectionAndTabWithErrorsIndexes;

      validateAllSections();

      sectionAndTabWithErrorsIndexes = findIndexesOfFirstSectionAndTabWithErrors();

      if (sectionAndTabWithErrorsIndexes) {
        notificationService.error('', 'There are errors on the form. Please fix them before continuing.');
        openSection(sectionAndTabWithErrorsIndexes.sectionIndex);
        openActiveSectionTab(sectionAndTabWithErrorsIndexes.tabIndex);

        return;
      }

      save();
    }

    /**
     * Validates a field
     *
     * @param {Object} field
     */
    function validateField (field) {
      flushErrorForField(field);

      if (field.required && _.isEmpty(field.value)) {
        field.error = 'This field is required';
      } else if (field.value !== '' && field.validations) {
        field.validations.forEach(function (validation) {
          if (!validation.rule.test(field.value)) {
            field.error = validation.message;
          }
        });
      }
    }

    /**
     * Validates all sections (the whole wizard)
     */
    function validateAllSections () {
      vm.sections.forEach(function (section) {
        section.tabs.forEach(function (tab) {
          validateTab(tab);
        });
      });
    }

    /**
     * Validates all fields for a tab
     * and checks if the whole tab is filled in correctly
     *
     * @param {Object} tab
     */
    function validateTab (tab) {
      tab.fields.forEach(function (field) {
        validateField(field);
      });

      tab.valid = !_.find(tab.fields, function (field) {
        return !field.hidden && field.error;
      });
    }

    /**
     * Initiates a watcher over the "Allow carry forward" field.
     * Toggles dependent fields on value change.
     */
    function watchAllowCarryForwardField () {
      $scope.$watch(function () {
        return vm.fieldsIndexed.allow_carry_forward.value;
      }, function (allowCarryForward) {
        vm.fieldsIndexed.carry_forward_expiration_duration_switch.hidden = !allowCarryForward;
        vm.fieldsIndexed.max_number_of_days_to_carry_forward.hidden = !allowCarryForward;
      });
    }

    /**
     * Initiates a watcher over the "Carry forward expiry" field.
     * Toggles dependent fields on value change of if gets toggled.
     */
    function watchCarryForwardExpirySwitch () {
      $scope.$watch(function () {
        return vm.fieldsIndexed.carry_forward_expiration_duration_switch;
      }, function (expirySwitch) {
        vm.fieldsIndexed.carry_forward_expiration_duration.hidden =
          !expirySwitch.value || expirySwitch.hidden;
      }, true);
    }

    /**
     * Initiates a watches over "Title" field.
     * Watches for already used leave types titles.
     */
    function watchTitleField () {
      var titleField = vm.fieldsIndexed.title;

      $scope.$watch(function () {
        return titleField.value;
      }, function (title) {
        if (_.isEmpty(title)) {
          return;
        }

        if (_.find(absenceTypes, { title: title })) {
          titleField.error = 'This leave type title is already in use';
        }
      });
    }
  }
});
