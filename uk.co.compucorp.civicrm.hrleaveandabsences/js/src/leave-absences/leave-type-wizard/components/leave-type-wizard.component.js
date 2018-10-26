/* eslint-env amd */

define([
  'common/angular',
  'common/lodash'
], function (angular, _) {
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

    var absenceTypesExistingTitles = [];
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
    vm.checkIfAccordionHeaderClicked = checkIfAccordionHeaderClicked;
    vm.openNextTab = openNextTab;
    vm.openPreviousTab = openPreviousTab;
    vm.openSection = openSection;
    vm.openActiveSectionTab = openActiveSectionTab;

    function $onInit () {
      vm.loading = true;

      $q.all([
        loadContacts(),
        loadAvailableColours(),
        loadAbsenceTypesExistingTitles()
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
     * Check if the header of the accordion was clicked and not other area
     *
     * @param  {Event} $event
     * @return {Boolean}
     */
    function checkIfAccordionHeaderClicked ($event) {
      var className = 'panel-heading';
      var $sourceElement = angular.element($event.originalEvent.path);
      var isHeaderOrElementInsideHeader = $sourceElement.hasClass(className) ||
        !!$sourceElement.closest('.' + className).length;

      return isHeaderOrElementInsideHeader;
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
      watchTitleFieldIsUnique();
      watchTitleFieldIsFilledInProperly();
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
     * - expands the General section and leaves the Settings section collapsed;
     * - selects Basic Details settings tab.
     */
    function initDefaultView () {
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
          : validateField(field, oldValue);
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
     * Fetches absence types and stores their titles in the component.
     * It also lowers the case of the titles.
     */
    function loadAbsenceTypesExistingTitles () {
      return AbsenceType.all({}, { return: ['title'] })
        .then(function (absenceTypes) {
          absenceTypesExistingTitles = absenceTypes.map(function (absenceType) {
            return absenceType.title.toLowerCase();
          });
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
      $window.location.href = CRM.url('civicrm/admin/leaveandabsences/types', {
        action: 'browse',
        reset: 1
      });
    }

    /**
     * Opens next section. If there are no more sections, then submits the form.
     */
    function openNextSection () {
      var isOnLastSection = state.sectionIndex === vm.sections.length - 1;

      if (isOnLastSection) {
        submit();

        return;
      }

      openSection(state.sectionIndex + 1);
    }

    /**
     * Opens the next tab in the active section
     */
    function openNextTab () {
      var activeTab = getActiveTab();

      validateTab(activeTab);
      openActiveSectionTab(state.tabIndex + 1);
    }

    /**
     * Opens previous section. If there are no sections behind, cancels form filling.
     */
    function openPreviousSection () {
      var isOnFirstSection = state.sectionIndex === 0;

      if (isOnFirstSection) {
        vm.loading = true;

        navigateToLeaveTypesList();

        return;
      }

      openSection(state.sectionIndex - 1);
    }

    /**
     * Opens the previous tab in the active section
     */
    function openPreviousTab () {
      openActiveSectionTab(state.tabIndex - 1);
    }

    /**
     * Opens a section by its index, collapses all other sections and,
     * if there are any tabs, opens the first tab.
     *
     * @param {Number} sectionIndex
     */
    function openSection (sectionIndex) {
      var sectionToOpen = vm.sections[sectionIndex];

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
     * - sets default entitlement to 0 if not provided
     * - flushes dependent fields' values
     * - deletes fields held for UX only
     *
     * @param {Object} params
     */
    function preProcessParams (params) {
      if (params.default_entitlement === '') {
        params.default_entitlement = '0';
      }

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
     * @param {String} [oldValue]
     */
    function validateField (field, oldValue) {
      flushErrorForField(field);

      if (field.required && _.isEmpty(field.value) && oldValue !== '') {
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
     * Watches title field to toggle Settings section locking
     */
    function watchTitleFieldIsFilledInProperly () {
      var titleField = vm.fieldsIndexed.title;

      $scope.$watch(function () {
        return titleField.value;
      }, function (title) {
        var disallowedToMoveToSettingsSection = !!(titleField.error || title === '');

        setAvailabilityOfFollowingSections(disallowedToMoveToSettingsSection);
      });
    }

    /**
     * Sets the availability of the sections that follow the active section.
     * Also sets the availability of the "Next section" button for the active section.
     *
     * @param {Boolean} isDisabled
     */
    function setAvailabilityOfFollowingSections (isDisabled) {
      vm.sections[state.sectionIndex].disableNextSectionButton = isDisabled;

      vm.sections.slice(state.sectionIndex + 1).forEach(function (section) {
        section.disabled = isDisabled;
      });
    }

    /**
     * Initiates a watches over "Title" field.
     * Watches for already used leave types titles.
     */
    function watchTitleFieldIsUnique () {
      var titleField = vm.fieldsIndexed.title;

      $scope.$watch(function () {
        return titleField.value;
      }, function (title) {
        if (_.isEmpty(title)) {
          return;
        }

        if (_.includes(absenceTypesExistingTitles, title.toLowerCase())) {
          titleField.error = 'This leave type title is already in use';
        }
      });
    }
  }
});
