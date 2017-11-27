/* eslint-env amd */

define([
  'common/angular',
  'common/lodash',
  'common/moment'
], function (angular, _, moment) {
  'use strict';

  JobRolesController.__name = 'JobRolesController';
  JobRolesController.$inject = [
    '$filter', '$log', '$q', '$rootElement', '$route', '$routeParams', '$scope',
    '$timeout', '$uibModal', 'DOMEventTrigger', 'settings', 'HR_settings',
    'dateValidation', 'filtersService', 'jobRoleService', 'pubSub'
  ];

  function JobRolesController ($filter, $log, $q, $rootElement, $route,
    $routeParams, $scope, $timeout, $modal, DOMEventTrigger, settings,
    hrSettings, dateValidation, filtersService, jobRoleService, pubSub) {
    $log.debug('Controller: JobRolesController');

    var formatDate = $filter('formatDate');
    var fundersContacts = {};
    var getActiveValues = $filter('getActiveValues');
    var rolesType = ['funders', 'cost_centers'];
    var vm = this;

    vm.contactId = settings.contactId;
    vm.format = hrSettings.DATE_FORMAT;
    vm.loading = true;
    vm.pastJobRoles = [];
    vm.presentJobRoles = [];
    vm.collapsedRows = []; // Tracks collapsed / expanded rows
    vm.contactList = []; // Contact List IDs array to use for the select lists
    vm.editData = {}; // Tracks edit data changes on the forms
    vm.viewTab = []; // Tracks clicked tabs per each row
    vm.CalendarShow = []; // As default hide the datepickers
    vm.contractsData = []; // Store the contractsData
    vm.DepartmentsData = {}; // Store the department types
    vm.LevelsData = {}; // Store the level types
    vm.LocationsData = {}; // Store the location types
    vm.RegionsData = {}; // Store the region types
      // Define the add new role URL
    vm.addNewRoleUrl = settings.pathBaseUrl + settings.pathIncludeTpl + 'add_new_role.html';
    vm.jobRolePanelUrl = settings.pathBaseUrl + settings.pathIncludeTpl + 'job_role_panel.html';
      // Select list for Row Types (used for Funders and Cost Centers)
    vm.rowTypes = {};
    vm.rowTypes[0] = { id: 0, name: 'Fixed' };
    vm.rowTypes[1] = { id: 1, name: '%' };

    vm.addAdditionalRow = addAdditionalRow;
    vm.addNewRole = addNewRole;
    vm.cancelNewRole = cancelNewRole;
    vm.changeTab = changeTab;
    vm.checkIfDatesAreCustom = checkIfDatesAreCustom;
    vm.checkNewRole = checkNewRole;
    vm.collapseRow = collapseRow;
    vm.deleteAdditionalRow = deleteAdditionalRow;
    vm.dpOpen = dpOpen;
    vm.getContactList = getContactList;
    vm.getCostLabel = getCostLabel;
    vm.initData = initData;
    vm.isChanged = isChanged;
    vm.isOpen = isOpen;
    vm.isRowCollapsed = isRowCollapsed;
    vm.isTab = isTab;
    vm.onAfterSave = onAfterSave;
    vm.onCancel = onCancel;
    vm.onContractEdited = onContractEdited;
    vm.onContractSelected = onContractSelected;
    vm.open = open;
    vm.removeRole = removeRole;
    vm.saveNewRole = saveNewRole;
    vm.select = select;
    vm.showRowType = showRowType;
    vm.showSave = showSave;
    vm.status = '';
    vm.today = today;
    vm.updateAdditionalRowType = updateAdditionalRowType;
    vm.updateRole = updateRole;
    vm.validateRole = validateRole;
    vm.validateTitle = validateTitle;

    (function init () {
      subcribeToEvents();
      vm.today();

      $q.all([
        getOptionValues(),
        getJobRolesList(vm.contactId),
        vm.getContactList()
      ])
        .then(function () {
          vm.loading = false;
        });
    }());

    /**
     * Add additional rows (funder or cost centres)
     *
     * @param {int} roleId
     * @param {string} rowType
     */
    function addAdditionalRow (roleId, rowType) {
      // Check if we have the array already
      if (typeof vm.editData[roleId] === 'undefined') {
        vm.editData[roleId] = {};
      }

      if (rowType === 'cost_centre') {
        // Add cost centres
        // Check if we have the array already
        if (typeof vm.editData[roleId]['cost_centers'] === 'undefined' || !(vm.editData[roleId]['cost_centers'] instanceof Array)) {
          vm.editData[roleId]['cost_centers'] = [];
        }

        vm.editData[roleId]['cost_centers'].push({
          id: vm.editData[roleId]['cost_centers'].length + 1,
          cost_centre_id: '',
          type: '1',
          percentage: '0',
          amount: '0'
        });
      } else {
        // As default add funder rows
        // Check if we have the array already
        if (typeof vm.editData[roleId]['funders'] === 'undefined' || !(vm.editData[roleId]['funders'] instanceof Array)) {
          vm.editData[roleId]['funders'] = [];
        }

        vm.editData[roleId]['funders'].push({
          id: vm.editData[roleId]['funders'].length + 1,
          funder_id: '',
          type: '1',
          percentage: '0',
          amount: '0'
        });
      }
    }

    /**
     * Sets the add new job role form visibility
     */
    function addNewRole () {
      vm.add_new = true;
    }

    /**
     * Hides the add new job role form and removes any data added.
     */
    function cancelNewRole () {
      vm.add_new = false;
      delete vm.editData['new_role_id'];
    }

    /**
     * Implement angular tabs
     *
     * @param  {int} rowId
     * @param  {int} tabId
     */
    function changeTab (rowId, tabId) {
      vm.viewTab[rowId] = tabId;
    }

    /**
     * Checks if dates don't exist in any of contracts
     * @param start
     * @param end
     * @returns {boolean}
     */
    function checkIfDatesAreCustom (start, end) {
      if (isDateEmpty(start)) start = null;
      if (isDateEmpty(end)) end = null;

      var custom = true;

      if (!start) return false;

      angular.forEach(vm.contractsData, function (value) {
        if (formatDate(start) === formatDate(value.start_date) &&
          formatDate(end) === formatDate(value.end_date)) {
          custom = false;
        }
      });

      return custom;
    }

    /**
     * Check if we allow to submit the form
     * Rule -> Allow only if the minimum required data are filled
     *
     * @return {boolean}
     */
    function checkNewRole () {
      return (typeof vm.editData['new_role_id'] === 'undefined' ||
        typeof vm.editData['new_role_id']['title'] === 'undefined' ||
        vm.editData['new_role_id']['title'] === '' ||
        typeof vm.editData['new_role_id']['job_contract_id'] === 'undefined' ||
        vm.editData['new_role_id']['job_contract_id'] === '');
    }

    /**
     * Collapse the row or Expand when clicked
     *
     * @param  {int} rowId
     */
    function collapseRow (rowId) {
      vm.collapsedRows[rowId] = !vm.collapsedRows[rowId];
    }

    /**
     * Fetches the contract ids of the given contact
     *
     * @param {int} contactId
     * @return {Promise} resolves with an array of contract ids
     */
    function contractIdsFromContact (contactId) {
      return jobRoleService.getContracts(contactId).then(function (data) {
        var jobContractIds = [];
        var contractsData = {};

        // If we have job contracts, try to get the job roles for the contract
        for (var i = 0; i < data.count; i++) {
          // Job contract IDs which will be passed to the "getAllJobRoles" service
          jobContractIds.push(data.values[i]['id']);

          var contract = {
            id: data.values[i]['id'],
            title: data.values[i]['title'],
            start_date: data.values[i]['period_start_date'],
            end_date: data.values[i]['period_end_date'],
            status: vm.status,
            is_current: data.values[i]['is_current'],
            revisions: data.values[i]['revisions']
          };

          var optionalEndDate = formatDate(contract.end_date) || 'Unspecified';

          contract.label = contract.title + ' (' + formatDate(contract.start_date) + ' - ' + optionalEndDate + ')';
          contractsData[data.values[i]['id']] = contract;
        }

        // Store the ContractsData what we can reuse later
        vm.contractsData = contractsData;
        vm.job_contract_ids = jobContractIds;

        return jobContractIds;
      }, function (errorMessage) {
        vm.error = errorMessage;
      });
    }

    /**
     * Parse dates so they can be correctly read by server.
     *
     * @param {string|Date} date
     * @returns {string|null}
     */
    function convertDateToServerFormat (date) {
      var dateString = formatDate(date, 'YYYY-MM-DD');

      return dateString !== 'Unspecified' ? dateString : null;
    }

    /**
     * Implements the "createJobRole" service
     *
     * @param  {Object} jobRolesData
     * @return {Promise}
     */
    function createJobRole (jobRolesData) {
      return jobRoleService.createJobRole(jobRolesData).then(function (data) {
        return data;
      }, function (errorMessage) {
        vm.error = errorMessage;
      });
    }

    /**
     * Delete Additional rows (funder or cost centres)
     *
     * @param  {int} roleId
     * @param  {string} rowType
     * @param  {int} rowId
     */
    function deleteAdditionalRow (roleId, rowType, rowId) {
      if (rowType === 'cost_centre') {
        // Remove the cost centre row
        vm.editData[roleId]['cost_centers'].splice(rowId, 1);
      } else {
        // Remove the funder row as default
        vm.editData[roleId]['funders'].splice(rowId, 1);
      }
    }

    /**
     * Implements the "deleteJobRole" service
     *
     * @param  {int} jobRoleId
     * @return {Promise}
     */
    function deleteJobRole (jobRoleId) {
      return jobRoleService.deleteJobRole(jobRoleId).then(function (data) {
        return data;
      },
      function (errorMessage) {
        vm.error = errorMessage;
      });
    }

    /**
     *
     * @param  {Object} $event
     */
    function dpOpen ($event) {
      $event.preventDefault();
      $event.stopPropagation();

      vm.picker.opened = true;
    }

    /**
     * Extracts, from each job role past and preset, the contact id of every funder
     *
     * It combines present and past job roles, make a list of the funder ids string,
     * splits it by the "|" separator, and return only the non-null and unique values
     *
     * @return {Array} a list of ids
     */
    function extractFundersContactIds () {
      return _(vm.presentJobRoles.concat(vm.pastJobRoles))
        .map(function (jobRole) {
          return jobRole.funder;
        })
        .thru(function (funderIds) {
          return funderIds.join('').split('|');
        })
        .compact().uniq().value();
    }

    /**
     * Filter the editData property to remove
     * the funders/cost_centers entries which are empty
     *
     * @param  {string|int} roleId
     * @param  {string} roleType
     */
    function filterEmptyData (roleId, roleType) {
      if (vm.editData.hasOwnProperty(roleId)) {
        if (roleType === 'funders') {
          vm.editData[roleId][roleType] = filtersService.issetFunder(vm.editData[roleId][roleType]);
        }

        if (roleType === 'cost_centers') {
          vm.editData[roleId][roleType] = filtersService.issetCostCentre(vm.editData[roleId][roleType]);
        }
      }
    }

    /**
     * Sets the values of the given role's start and end date properties
     * to the Date objects representing the given start and end dates
     *
     * @param {Object} role - The job role
     * @param {Object} dates - An object with `start` and `end ` dates
     * @param {Object} keys - Custom names of the role's start and end date properties
     */
    function formatRoleDates (role, dates, keys) {
      keys = keys || { start: 'start_date', end: 'end_date' };

      role[keys.start] = dates.start ? formatDate(dates.start, Date) : null;
      role[keys.end] = dates.end ? formatDate(dates.end, Date) : null;
    }

    /**
     * Get the contact list and store the data
     *
     * @param  {string} sortName
     */
    function getContactList (sortName) {
      var successCallback = function (data) {
        var contactList = [];
        var i = 0;

        if (data.is_error === 1) {
          vm.message_type = 'alert-danger';
          vm.message = 'Cannot get contact list!';
        } else {
          // Pass the contact list to the scope
          for (; i < data.count; i++) {
            contactList.push({
              id: data.values[i]['id'],
              sort_name: data.values[i]['sort_name']
            });
          }

          // Store the ContactList as Array as typeahead needs array that we can reuse later
          vm.contactList = contactList;
        }

        // Hide the message after some seconds
        $timeout(function () {
          vm.message = null;
        }, 3000);
      };

      var errorCallback = function (errorMessage) {
        vm.error = errorMessage;
      };

      return jobRoleService.getContactList(sortName).then(successCallback, errorCallback);
    }

    /**
     * Get a contract with the given contractId
     *
     * @param {int} contractId
     * @returns {object}
     */
    function getContractData (contractId) {
      return vm.contractsData[contractId];
    }

    /**
     *
     * @param  {int} id
     * @return {string}
     */
    function getCostLabel (id) {
      var label = '';
      angular.forEach(vm.CostCentreList, function (v, k) {
        if (+v.id === +id) {
          label = v.title;
        }
      });

      return label;
    }

    /**
     * Fetches from the API the contact data of all the funders of each job role
     *
     * @return {Promise}
     *   resolves to an object, with the key as the contact id,
     *   and the value as the contact data
     */
    function getFundersContacts () {
      return jobRoleService.getContactList(null, extractFundersContactIds()).then(function (data) {
        return _(data.values).map(function (contact) {
          return contact;
        })
        .indexBy('contact_id')
        .value();
      });
    }

    /**
     * Get job roles based on the passed Contact ID (refresh part of the page)
     *
     * @param {int} contactId
     */
    function getJobRolesList (contactId) {
      var contractsPromise;

      if (!vm.job_contract_ids) {
        contractsPromise = contractIdsFromContact(contactId);
      } else {
        contractsPromise = $q.when(vm.job_contract_ids);
      }

      return contractsPromise.then(function (contractIds) {
        return !!contractIds.length && jobRolesFromContracts(contractIds);
      });
    }

    /**
     *
     */
    function getOptionValues () {
      // Set the option groups for which we want to get the values
      var optionGroups = ['hrjc_department', 'hrjc_region', 'hrjc_location', 'hrjc_level_type', 'cost_centres'];

      return jobRoleService.getOptionValues(optionGroups).then(function (data) {
        if (data.is_error === 1) {
          vm.message_type = 'alert-danger';
          vm.message = 'Cannot get option values!';
        } else {
          // Pass the department option group list to the scope
          var DepartmentList = {};

          // Pass the region option group list to the scope
          var RegionList = {};

          // Pass the location option group list to the scope
          var LocationList = {};

          // Pass the level option group list to the scope
          var LevelList = {};

          // Pass the Cost Centers option group list to the scope
          var CostCentreList = [];

          angular.forEach(data['optionGroupData'], function (optionGroupId, optionGroupName) {
            for (var i = 0; i < data.count; i++) {
              switch (optionGroupName) {
                case 'hrjc_department':
                  if (optionGroupId === data.values[i]['option_group_id']) {
                    // Build the department list
                    DepartmentList[data.values[i]['value']] = {
                      id: data.values[i]['id'],
                      title: data.values[i]['label'],
                      value: data.values[i]['value'],
                      is_active: data.values[i]['is_active']
                    };
                  }

                  break;
                case 'hrjc_region':
                  if (optionGroupId === data.values[i]['option_group_id']) {
                    // Build the region list
                    RegionList[data.values[i]['value']] = {
                      id: data.values[i]['id'],
                      title: data.values[i]['label'],
                      value: data.values[i]['value'],
                      is_active: data.values[i]['is_active']
                    };
                  }

                  break;
                case 'hrjc_location':
                  if (optionGroupId === data.values[i]['option_group_id']) {
                    // Build the contact list
                    LocationList[data.values[i]['value']] = {
                      id: data.values[i]['id'],
                      title: data.values[i]['label'],
                      value: data.values[i]['value'],
                      is_active: data.values[i]['is_active']
                    };
                  }

                  break;
                case 'hrjc_level_type':
                  if (optionGroupId === data.values[i]['option_group_id']) {
                    // Build the contact list
                    LevelList[data.values[i]['value']] = {
                      id: data.values[i]['id'],
                      title: data.values[i]['label'],
                      value: data.values[i]['value'],
                      is_active: data.values[i]['is_active']
                    };
                  }

                  break;
                case 'cost_centres':
                  if (optionGroupId === data.values[i]['option_group_id']) {
                    // Build the contact list
                    CostCentreList.push({
                      id: data.values[i]['id'],
                      title: data.values[i]['label'],
                      is_active: data.values[i]['is_active'],
                      weight: data.values[i]['weight']
                    });
                  }

                  break;
              }
            }
          });

          // Store the Department types so we can reuse later
          vm.DepartmentsData = getActiveValues(DepartmentList);

          // Store the Region types so we can reuse later
          vm.RegionsData = getActiveValues(RegionList);

          // Store the Location types so we can reuse later
          vm.LocationsData = getActiveValues(LocationList);

          // Store the Level types so we can reuse later
          vm.LevelsData = getActiveValues(LevelList);

          // Store the cost center list so we can reuse later
          vm.CostCentreList = CostCentreList;

          vm.message_type = 'alert-success';
          vm.message = null;
        }

        // Hide the message after some seconds
        $timeout(function () {
          vm.message = null;
        }, 3000);
      },
      function (errorMessage) {
        vm.error = errorMessage;
      });
    }

    /**
     * Initializes the cost centers data in the given job role
     *
     * @param  {Object} jobRole
     * @param  {Object} data
     */
    function initCostCentersData (jobRole, data) {
      jobRole.cost_centers = [];

      var costCenterContactIds = filtersService.isNotUndefined(data.cost_center.split('|'));
      var costCenterTypes = data.cost_center_val_type.split('|');
      var percentCostCenters = data.percent_pay_cost_center.split('|');
      var amountCostCenters = data.amount_pay_cost_center.split('|');

      for (var i = 0; i < costCenterContactIds.length; i++) {
        if (costCenterContactIds[i] !== '') {
          jobRole.cost_centers.push({
            id: jobRole.cost_centers.length + 1,
            amount: amountCostCenters[i],
            cost_centre_id: costCenterContactIds[i],
            percentage: percentCostCenters[i],
            type: costCenterTypes[i]
          });
        }
      }
    }

    /**
     * Set the data from the webservice call
     *
     * @param  {int} roleId
     * @param  {int} formId
     * @param  {*} data
     */
    function initData (roleId, formId, data) {
      // Check if we have the array already
      if (typeof vm.editData[roleId] === 'undefined') {
        vm.editData[roleId] = {
          'role_id': roleId
        };
      }

      if (formId === 'funders') {
        initFundersData(vm.editData[roleId], data);
      } else if (formId === 'cost_centers') {
        initCostCentersData(vm.editData[roleId], data);
      } else {
        initMiscData(vm.editData[roleId], formId, data);
      }

      if (formId === 'end_date' && !vm.editData[roleId].end_date) {
        vm.editData[roleId].end_date = null;
      }

      if (vm.editData[roleId].job_contract_id &&
        vm.editData[roleId].start_date &&
        typeof vm.editData[roleId].end_date !== 'undefined' &&
        (formId === 'start_date' || formId === 'job_contract_id' || formId === 'end_date')) {
        updateRolesWithContractData(roleId);
      }
    }

    /**
     * Initializes the funders data in the given job role
     *
     * @param  {Object} jobRole
     * @param  {Object} data
     */
    function initFundersData (jobRole, data) {
      jobRole.funders = [];

      var funderContactIds = filtersService.isNotUndefined(data.funder.split('|'));
      var funderTypes = data.funder_val_type.split('|');
      var percentFunders = data.percent_pay_funder.split('|');
      var amountFunders = data.amount_pay_funder.split('|');

      for (var i = 0; i < funderContactIds.length; i++) {
        if (funderContactIds[i] !== '') {
          jobRole.funders.push({
            id: jobRole.funders.length + 1,
            amount: amountFunders[i],
            percentage: percentFunders[i],
            type: funderTypes[i],
            funder_id: {
              id: funderContactIds[i],
              sort_name: fundersContacts[funderContactIds[i]].sort_name
            }
          });
        }
      }
    }

    /**
     * Initializes miscellaneous data in the given job role
     *
     * @param  {Object} jobRole
     * @param  {string} key
     * @param  {Object} data
     */
    function initMiscData (jobRole, key, data) {
      var bothJustSet = (typeof jobRole.start_date === 'undefined' ||
        typeof jobRole.job_contract_id === 'undefined');

      // Default data init
      jobRole[key] = data;

      if (jobRole.start_date) {
        // If dates are not set, we programatically set them here
        var date = moment(jobRole.start_date);
        var invalidDate = (isNaN(date) && typeof jobRole.start_date !== 'undefined');

        var presentJobContract = !(typeof jobRole.job_contract_id === 'undefined');

        if (invalidDate && presentJobContract && bothJustSet) {
          vm.onContractEdited(null, jobRole.job_contract_id).then(function () {
            $scope.$apply();
            return vm.updateRole(jobRole.role_id);
          });
        } else {
          formatRoleDates(jobRole, {
            start: jobRole.start_date,
            end: jobRole.end_date
          });
        }
      }
    }

    /**
     * Check if the data are changed in the form (based on job role ID)
     * @param rowId
     * @returns {boolean}
       */
    function isChanged (rowId) {
      // If there are data it means we edited the form
      return !!(vm.editData[rowId]['is_edit']);
    }

    /**
     * Checks if date should be considered empty.
     *
     * @param {String} date
     * @returns {boolean}
     */
    function isDateEmpty (date) {
      return date === null;
    }

    /**
     *
     * @param  {string}  name
     * @return {Boolean}
     */
    function isOpen (name) {
      return !!(vm.CalendarShow[name]);
    }

    /**
     * Check for collapsed rows
     *
     * @param  {int}  rowId
     * @return {Boolean}
     */
    function isRowCollapsed (rowId) {
      return !!(vm.collapsedRows[rowId]);
    }

    /**
     * Check if current tab
     *
     * @param  {int}  rowId
     * @param  {int}  tabId
     * @return {Boolean}
     */
    function isTab (rowId, tabId) {
      return (+vm.viewTab[rowId] === +tabId);
    }

    /**
     * Called on angular-xeditable's onaftersave callback.
     * It'll filter the rows which are without data.
     *
     * @param  {string|int} roleId
     * @param  {string} roleType
     */
    function onAfterSave (roleId, roleType) {
      filterEmptyData(roleId, roleType);
    }

    /**
     * Called on angular-xeditable's cancel callback.
     * It'll filter the rows which are without data.
     *
     * @param  {string|int} roleId
     * @param  {string} roleType
     */
    function onCancel (roleId, roleType) {
      if (roleType === 'both') {
        rolesType.map(function (type) {
          filterEmptyData(roleId, type);
        });
      } else {
        filterEmptyData(roleId, roleType);
      }
    }

    /**
     * Method responsible for updating existing JobRole with dates from Contract
     * @param jobContractId
     * @param roleId
     */
    function onContractEdited (jobContractId, roleId) {
      var id = jobContractId || vm.editData[roleId]['job_contract_id'];
      var contract = getContractData(id);
      var areDatesCustom = vm.checkIfDatesAreCustom(vm.editData[roleId]['start_date'], vm.editData[roleId]['end_date']);

      if (contract === undefined) {
        vm.editData[roleId]['job_contract_id'] = undefined;
        vm.editData[roleId]['start_date'] = undefined;
        vm.editData[roleId]['end_date'] = undefined;

        return false;
      }

      if (!areDatesCustom) {
        formatRoleDates(vm.editData[roleId], {
          start: contract.start_date,
          end: contract.end_date
        });
      } else {
        formatRoleDates(vm.editData[roleId], {
          start: vm.editData[roleId].start_date,
          end: vm.editData[roleId].end_date
        });
      }
    }

    /**
     * Method responsible for updating new JobRole with dates from Contract
     */
    function onContractSelected () {
      var contract = getContractData(vm.editData.new_role_id.job_contract_id);
      var areDatesCustom = vm.checkIfDatesAreCustom(vm.editData.new_role_id.newStartDate, vm.editData.new_role_id.newEndDate);
      if (contract === undefined) {
        vm.editData['new_role_id']['job_contract_id'] = undefined;
        vm.editData['new_role_id']['newStartDate'] = undefined;
        vm.editData['new_role_id']['newEndDate'] = undefined;
      } else {
        formatRoleDates(vm.editData.new_role_id, {
          start: areDatesCustom ? vm.editData.new_role_id.newStartDate : contract.start_date,
          end: areDatesCustom ? vm.editData.new_role_id.newEndDate : contract.end_date
        },
          {
            start: 'newStartDate',
            end: 'newEndDate'
          });
      }
    }

    /**
     *
     * @param  {Object} event
     */
    function open (event) {
      vm.CalendarShow[event] = true;
    }

    /**
     * Fetches the job roles of the contracts with the given ids
     *
     * @param {Array} contractIds
     * @return {Promise}
     */
    function jobRolesFromContracts (contractIds) {
      return jobRoleService.getAllJobRoles(contractIds)
        .then(function (data) {
          vm.presentJobRoles = [];
          vm.pastJobRoles = [];
          vm.status = 'Data load OK';

          if (data.is_error === 1) {
            vm.error = 'Data load failure';
          }

          data.values.forEach(function (objectData) {
            var todaysDate = moment().startOf('day');
            var endDate = null;

            if (!isDateEmpty(objectData.end_date)) {
              endDate = moment(objectData.end_date).startOf('day');
            }

            if (!endDate || endDate.isSameOrAfter(todaysDate)) {
              vm.presentJobRoles.push(objectData);
            } else {
              vm.pastJobRoles.push(objectData);
            }
          });
        })
        .then(function () {
          return getFundersContacts();
        })
        .then(function (contacts) {
          fundersContacts = contacts;
        })
        .catch(function (errorMessage) {
          vm.error = errorMessage;
        });
    }

    /**
     * Removes the given Role
     *
     * @param {Object} jobRole
     */
    function removeRole (jobRole) {
      $log.debug('Remove Role');

      var modalInstance = $modal.open({
        appendTo: $rootElement.find('div').eq(0),
        template: '',
        templateUrl: settings.pathApp + 'views/modalDialog.html?v=' + (new Date()).getTime(),
        size: 'sm',
        controller: 'ModalDialogController',
        controllerAs: 'dialog',
        resolve: {
          content: function () {
            return {
              copyCancel: 'No',
              title: 'Alert',
              msg: 'Are you sure you want to Delete Job Role?'
            };
          }
        }
      });

      // Delete job role
      modalInstance.result.then(function (confirm) {
        if (confirm) {
          deleteJobRole(jobRole.id).then(function () {
            updateHeaderInfo(jobRole);
            pubSub.publish('JobRole::deleted');

            return getJobRolesList(vm.contactId);
          });
        }
      });
    }

    /**
     * Validates Dates and saves the new Job Role
     */
    function saveNewRole () {
      var newRole;

      $log.debug('Add New Role');

      vm.errors = {};
      vm.errors.newStartDate = [];
      vm.errors.newEndDate = [];

      var contract = getContractData(vm.editData.new_role_id.job_contract_id);
      var validateResponse = validateDates({
        'start': vm.editData.new_role_id.newStartDate,
        'end': vm.editData.new_role_id.newEndDate,
        'contractStart': contract.start_date,
        'contractEnd': contract.end_date
      },
        {
          'start': vm.errors.newStartDate,
          'end': vm.errors.newEndDate
        });

      if (validateResponse) {
        newRole = angular.copy(vm.editData.new_role_id);
        newRole.newStartDate = convertDateToServerFormat(newRole.newStartDate);

        if (newRole.newEndDate) {
          newRole.newEndDate = convertDateToServerFormat(newRole.newEndDate);
        } else {
          delete newRole.newEndDate;
        }

        if (newRole.funders && newRole.funders.length) {
          updateFundersContactsList(newRole.funders);
        }

        createJobRole(newRole).then(function () {
          updateHeaderInfo(newRole);
          pubSub.publish('JobRole::created');

          // Hide the add new form
          vm.add_new = false;

          // Remove if any data are added / Reset form
          delete vm.editData['new_role_id'];

          return getJobRolesList(vm.contactId);
        });
      }
    }

    /**
     *
     * @param  {Object} event
     */
    function select (event) {
      vm.CalendarShow[event] = false;
    }

    /**
     * Show Row Type default value
     *
     * @param object
     * @returns {string}
     */
    function showRowType (object) {
      var selected = '';

      if (typeof object.type !== 'undefined') {
        // Get the human readable Type Value
        selected = vm.rowTypes[object.type];

        return selected.name;
      }

      return 'Not set';
    }

    /**
     * Set the is_edit value
     *
     * @param {int} rowId
     */
    function showSave (rowId) {
      vm.editData[rowId]['is_edit'] = true;
    }

    /**
     * Subscribes to external events
     */
    function subcribeToEvents () {
      // Triggers when a new contract is created for a contact.
      pubSub.subscribe('Contract::created', function (contactId) {
        contractIdsFromContact(contactId);
      });

      // Triggers when a contract is deleted for a contact.
      pubSub.subscribe('Contract::deleted', function (data) {
        contractIdsFromContact(data.contactId).then(function (contractIds) {
          if (!contractIds.length) {
            vm.presentJobRoles = [];
            vm.pastJobRoles = [];
          } else {
            return jobRolesFromContracts(contractIds);
          }
        });
      });
    }

    /**
     *
     */
    function today () {
      vm.CalendarShow['newStartDate'] = false;
      vm.CalendarShow['newEndDate'] = false;
      vm.CalendarShow['start_date'] = false;
      vm.CalendarShow['end_date'] = false;
    }

    /**
     * Update funder type scope on request
     *
     * @param  {int} roleId
     * @param  {string} rowType
     * @param  {string} key
     * @param  {*} data
     */
    function updateAdditionalRowType (roleId, rowType, key, data) {
      if (rowType === 'cost_centre') {
        // Update cost centers row
        vm.editData[roleId]['cost_centers'][key]['type'] = data;
      } else {
        // Update funder Type scope as default
        vm.editData[roleId]['funders'][key]['type'] = data;
      }
    }

    /**
     * Updates the internal list of funders contacts with the funders list
     * of the given job role. If the job role has any funder which is not
     * already stored in the list, the funder gets added
     *
     * @param  {Array} jobRoleFunders
     */
    function updateFundersContactsList (jobRoleFunders) {
      jobRoleFunders.forEach(function (funder) {
        var funderData = funder.funder_id;

        if (!_.contains(Object.keys(fundersContacts), funderData.id)) {
          fundersContacts[funderData.id] = funderData;
        }
      });
    }

    /**
     * Triggers the update of the contact header via the `hrui` extension
     * by emitting a DOM event with the roles data
     *
     * Given that the header reflects data only related to the current contract,
     * the header update happens only for job roles belonging that particular contract
     */
    function updateHeaderInfo (jobRole) {
      if (vm.contractsData[jobRole.job_contract_id].is_current) {
        jobRoleService.getCurrentDepartments(jobRole.job_contract_id).then(function (departments) {
          DOMEventTrigger('updateContactHeader', {
            roles: { departments: departments }
          });
        });
      }
    }

    /**
     * Implements the "updateJobRole" service
     *
     * @param  {int} roleId
     * @param  {Object} jobRolesData
     * @return {Promise}
     */
    function updateJobRole (roleId, jobRolesData) {
      return jobRoleService.updateJobRole(roleId, jobRolesData).then(function (data) {
        return data;
      }, function (errorMessage) {
        vm.error = errorMessage;
      });
    }

    /**
     * Prepares data and updates existing role
     *
     * @param {int} roleId
     * @param {string} roleType
     */
    function updateRole (roleId, roleType) {
      var updatedRole;

      $log.debug('Update Role');

      if (typeof roleType === 'string') {
        filterEmptyData(roleId, roleType);
      }

      updatedRole = angular.copy(vm.editData[roleId]);
      updatedRole.location = (updatedRole.location === undefined) ? updatedRole.location = '' : updatedRole.location;
      updatedRole.level = (updatedRole.level === undefined) ? updatedRole.level = '' : updatedRole.level;
      updatedRole.department = (updatedRole.department === undefined) ? updatedRole.department = '' : updatedRole.department;
      updatedRole.region = (updatedRole.region === undefined) ? updatedRole.region = '' : updatedRole.region;
      updatedRole.start_date = convertDateToServerFormat(updatedRole.start_date);

      if (updatedRole.end_date) {
        updatedRole.end_date = convertDateToServerFormat(updatedRole.end_date);
      } else {
        delete updatedRole.end_date;
      }

      if (updatedRole.funders && updatedRole.funders.length) {
        updateFundersContactsList(updatedRole.funders);
      }

      updateJobRole(roleId, updatedRole).then(function () {
        updateHeaderInfo(updatedRole);
        pubSub.publish('JobRole::updated');

        return getJobRolesList(vm.contactId);
      });
    }

    /**
     * Checks if JobRole dates are actual, if not checks if they exist in any of contract's revisions.
     *
     * @param {int} roleId
     */
    function updateRolesWithContractData (roleId) {
      var contractId = vm.editData[roleId].job_contract_id;

      if (vm.checkIfDatesAreCustom(vm.editData[roleId]['start_date'], vm.editData[roleId]['end_date'])) {
        var contract = getContractData(contractId);

        // search for revision containing these dates
        var revision = contract.revisions.some(function (rev) {
          return rev.period_start_date === formatDate(vm.editData[roleId]['start_date']) &&
              rev.period_end_date === formatDate(vm.editData[roleId]['end_date']);
        });

        // check if dates match with revision
        if (revision) {
          formatRoleDates(vm.editData[roleId], {
            start: contract.start_date,
            end: contract.end_date
          });

          vm.updateRole(roleId);
        }
      } else {
        formatRoleDates(vm.editData[roleId], {
          start: vm.editData[roleId].start_date,
          end: vm.editData[roleId].end_date
        });
      }
    }

    /**
     * Trigger validation on JobRole Dates + attach error callback
     *
     * @param {object} data - The dates to validate
     * @param {object} errors - The error recipients
     * @returns {boolean}
     */
    function validateDates (data, errors) {
      var errorsCount = 0;

      dateValidation.setErrorCallback(function (error, field) {
        errorsCount++;
        if (field.indexOf('start_date') > -1) {
          errors.start.push(error);
        }
        if (field.indexOf('end_date') > -1) {
          errors.end.push(error);
        }
      });
      dateValidation.validate(data.start, data.end, data.contractStart, data.contractEnd);

      return (errorsCount === 0);
    }

    /**
     * Validation method for JobRole data.
     * If string is returned form is not submitted.
     *
     * @param {Object} data
     * @return {boolean|string}
     */
    function validateRole (data) {
      // Reset Error Messages
      data.start_date.$error.custom = [];
      data.end_date.$error.custom = [];
      var contract = getContractData(data.contract.$viewValue);

      if (typeof contract === 'undefined') {
        return 'Contract is missing';
      }

      var validateResponse = validateDates({
        'start': data.start_date.$viewValue,
        'end': data.end_date.$viewValue,
        'contractStart': contract.start_date,
        'contractEnd': contract.end_date
      },
        {
          'start': data.start_date.$error.custom,
          'end': data.end_date.$error.custom
        });

      return (validateResponse ? true : 'Error');
    }

    /**
     *
     * @param {string} title
     * @returns {string|undefined}
     */
    function validateTitle (title) {
      if (title === 'title' || title === ' ') {
        return 'Title cannot be title!';
      }
    }
  }

  return JobRolesController;
});
