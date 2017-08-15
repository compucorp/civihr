define([
  'common/angular',
  'job-roles/controllers/controllers',
  'common/moment',
  'common/lodash',
  'common/filters/angular-date/format-date',
  'job-roles/filters/get-active-values.filter',
  'common/services/pub-sub'
], function (angular, controllers, moment, _) {
  'use strict';

  controllers.controller('HRJobRolesController', [
    '$scope', '$log', '$routeParams', '$route', '$uibModal', '$rootElement', '$timeout', '$filter', '$q','settings',
    'HR_settings', 'HRJobRolesService', 'DateValidation', 'HRJobRolesServiceFilters',
    'DOMEventTrigger', 'pubSub',
    function ($scope, $log, $routeParams, $route, $modal, $rootElement, $timeout, $filter, $q, settings, HR_settings, HRJobRolesService, DateValidation, HRJobRolesServiceFilters, DOMEventTrigger, pubSub) {
      $log.debug('Controller: HRJobRolesController');

      var vm = this;
      var formatDate = $filter('formatDate');
      var getActiveValues = $filter('getActiveValues');
      var fundersContacts = {};
      var roles_type = ['funders', 'cost_centers'];

      vm.contactId = settings.contactId;
      vm.format = HR_settings.DATE_FORMAT;
      vm.loading = true;
      vm.past_job_roles = [];
      vm.present_job_roles = [];
      vm.collapsedRows = []; // Tracks collapsed / expanded rows
      vm.contactList = []; // Contact List IDs array to use for the select lists
      vm.edit_data = {}; // Tracks edit data changes on the forms
      vm.view_tab = []; // Tracks clicked tabs per each row
      vm.CalendarShow = []; // As default hide the datepickers
      vm.contractsData = []; // Store the contractsData
      vm.DepartmentsData = {}; // Store the department types
      vm.LevelsData = {}; // Store the level types
      vm.LocationsData = {}; // Store the location types
      vm.RegionsData = {}; // Store the region types

      // Define the add new role URL
      vm.add_new_role_url = settings.pathBaseUrl + settings.pathIncludeTpl + 'add_new_role.html';
      vm.job_role_panel_url = settings.pathBaseUrl + settings.pathIncludeTpl + 'job_role_panel.html';

      // Select list for Row Types (used for Funders and Cost Centers)
      vm.rowTypes = {};
      vm.rowTypes[0] = { id: 0, name: 'Fixed' };
      vm.rowTypes[1] = { id: 1, name: '%' };

      /**
       * Add additional rows (funder or cost centres)
       *
       * @param {int} role_id
       * @param {string} row_type
       */
      vm.addAdditionalRow = function (role_id, row_type) {
        // Check if we have the array already
        if (typeof vm.edit_data[role_id] === "undefined") {
          vm.edit_data[role_id] = {};
        }

        if (row_type === 'cost_centre') {

          // Add cost centres
          // Check if we have the array already
          if (typeof vm.edit_data[role_id]['cost_centers'] === "undefined" || !(vm.edit_data[role_id]['cost_centers'] instanceof Array)) {
            vm.edit_data[role_id]['cost_centers'] = [];
          }

          vm.edit_data[role_id]['cost_centers'].push({
            id: vm.edit_data[role_id]['cost_centers'].length + 1,
            cost_centre_id: '',
            type: "1",
            percentage: "0",
            amount: "0"
          });
        } else {

          // As default add funder rows
          // Check if we have the array already
          if (typeof vm.edit_data[role_id]['funders'] === "undefined" || !(vm.edit_data[role_id]['funders'] instanceof Array)) {
            vm.edit_data[role_id]['funders'] = [];
          }

          vm.edit_data[role_id]['funders'].push({
            id: vm.edit_data[role_id]['funders'].length + 1,
            funder_id: '',
            type: "1",
            percentage: "0",
            amount: "0"
          });
        }
      };

      /**
       * Sets the add new job role form visibility
       */
      vm.add_new_role = function () {
        vm.add_new = true;
      };

      /**
       * Hides the add new job role form and removes any data added.
       */
      vm.cancelNewRole = function () {
        vm.add_new = false;
        delete vm.edit_data['new_role_id'];
      };

      /**
       * Checks if dates don't exist in any of contracts
       * @param start
       * @param end
       * @returns {boolean}
       */
      vm.checkIfDatesAreCustom = function (start, end) {
        if (isDateEmpty(start)) start = null;
        if (isDateEmpty(end)) end = null;

        var custom = true;

        if (!start) return false;


        angular.forEach(vm.contractsData, function (value) {
          if (formatDate(start) === formatDate(value.start_date)
            && formatDate(end) === formatDate(value.end_date))
            custom = false;
        });

        return custom;
      };

      /**
       * Implement angular tabs
       *
       * @param  {int} row_id
       * @param  {int} tab_id
       */
      vm.changeTab = function (row_id, tab_id) {
        vm.view_tab[row_id] = tab_id;
      };

      /**
       * Check if we allow to submit the form
       * Rule -> Allow only if the minimum required data are filled
       *
       * @return {boolean}
       */
      vm.checkNewRole = function () {
        return (typeof vm.edit_data['new_role_id'] === 'undefined'
        || typeof vm.edit_data['new_role_id']['title'] === 'undefined'
        || vm.edit_data['new_role_id']['title'] === ''
        || typeof vm.edit_data['new_role_id']['job_contract_id'] === 'undefined'
        || vm.edit_data['new_role_id']['job_contract_id'] === '');
      };

      /**
       * Collapse the row or Expand when clicked
       *
       * @param  {int} row_id
       */
      vm.collapseRow = function (row_id) {
        vm.collapsedRows[row_id] = !vm.collapsedRows[row_id];
      };

      /**
       * Delete Additional rows (funder or cost centres)
       *
       * @param  {int} role_id
       * @param  {string} row_type
       * @param  {int} row_id
       */
      vm.deleteAdditionalRow = function (role_id, row_type, row_id) {
        if (row_type === 'cost_centre') {
          // Remove the cost centre row
          vm.edit_data[role_id]['cost_centers'].splice(row_id, 1);
        } else {
          // Remove the funder row as default
          vm.edit_data[role_id]['funders'].splice(row_id, 1);
        }
      };

      /**
       *
       * @param  {Object} $event
       */
      vm.dpOpen = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();

        vm.picker.opened = true;
      };

      /**
       *
       * @param  {int} id
       * @return {string}
       */
      vm.getCostLabel = function (id) {
        var label = '';
        angular.forEach(vm.CostCentreList, function (v, k) {
          if (v.id == id) {
            label = v.title;
          }
        });

        return label;
      };

      /**
       * Set the data from the webservice call
       *
       * @param  {int} role_id
       * @param  {int} form_id
       * @param  {*} data
       */
      vm.initData = function (role_id, form_id, data) {
        // Check if we have the array already
        if (typeof vm.edit_data[role_id] === "undefined") {
          vm.edit_data[role_id] = {};
        }

        if (form_id === 'funders') {
          initFundersData(vm.edit_data[role_id], data);
        } else if (form_id === 'cost_centers') {
          initCostCentersData(vm.edit_data[role_id], data);
        } else {
          initMiscData(vm.edit_data[role_id], form_id, data);
        }

        if (form_id === 'end_date' && !vm.edit_data[role_id].end_date) {
          vm.edit_data[role_id].end_date = null;
        }

        if (vm.edit_data[role_id].job_contract_id
          && vm.edit_data[role_id].start_date
          && typeof vm.edit_data[role_id].end_date != 'undefined'
          && (form_id === 'start_date' || form_id === 'job_contract_id' || form_id === 'end_date')) {

          updateRolesWithContractData(role_id);
        }
      };

      /**
       * Check if the data are changed in the form (based on job role ID)
       * @param row_id
       * @returns {boolean}
       */
      vm.isChanged = function (row_id) {
        // If there are data it means we edited the form
        return !!(vm.edit_data[row_id]['is_edit']);
      };

      /**
       *
       * @param  {string}  name
       * @return {Boolean}
       */
      vm.isOpen = function (name) {
        return !!(vm.CalendarShow[name]);
      };

      /**
       * Check for collapsed rows
       *
       * @param  {int}  row_id
       * @return {Boolean}
       */
      vm.isRowCollapsed = function (row_id) {
        return !!(vm.collapsedRows[row_id]);
      };

      /**
       * Check if current tab
       *
       * @param  {int}  row_id
       * @param  {int}  tab_id
       * @return {Boolean}
       */
      vm.isTab = function (row_id, tab_id) {
        return (vm.view_tab[row_id] == tab_id);
      };

      /**
       * Called on angular-xeditable's onaftersave callback.
       * It'll filter the rows which are without data.
       *
       * @param  {string|int} role_id
       * @param  {string} role_type
       */
      vm.onAfterSave = function (role_id, role_type) {
        filterEmptyData(role_id, role_type);
      };

      /**
       * Called on angular-xeditable's cancel callback.
       * It'll filter the rows which are without data.
       *
       * @param  {string|int} role_id
       * @param  {string} role_type
       */
      vm.onCancel = function (role_id, role_type) {
        if (role_type === 'both') {
          roles_type.map(function (type) {
            filterEmptyData(role_id, type);
          });
        } else {
          filterEmptyData(role_id, role_type);
        }
      };

      /**
       * Method responsible for updating existing JobRole with dates from Contract
       * @param jobContractId
       * @param role_id
       */
      vm.onContractEdited = function (jobContractId, role_id) {
        var id = jobContractId || vm.edit_data[role_id]['job_contract_id'];
        var contract = getContractData(id);
        var areDatesCustom = vm.checkIfDatesAreCustom(vm.edit_data[role_id]['start_date'], vm.edit_data[role_id]['end_date']);

        if (contract === undefined) {
          vm.edit_data[role_id]['job_contract_id'] = undefined;
          vm.edit_data[role_id]['start_date'] = undefined;
          vm.edit_data[role_id]['end_date'] = undefined;

          return false;
        }

        if (!areDatesCustom) {
          formatRoleDates(vm.edit_data[role_id], {
            start: contract.start_date,
            end: contract.end_date
          });
        } else {
          formatRoleDates(vm.edit_data[role_id], {
            start: vm.edit_data[role_id].start_date,
            end: vm.edit_data[role_id].end_date
          });
        }
      };

      /**
       * Method responsible for updating new JobRole with dates from Contract
       */
      vm.onContractSelected = function () {
        var contract = getContractData(vm.edit_data.new_role_id.job_contract_id);
        var areDatesCustom = vm.checkIfDatesAreCustom(vm.edit_data.new_role_id.newStartDate, vm.edit_data.new_role_id.newEndDate);
        if(contract === undefined){
          vm.edit_data['new_role_id']['job_contract_id'] = undefined;
          vm.edit_data['new_role_id']['newStartDate'] = undefined;
          vm.edit_data['new_role_id']['newEndDate'] = undefined;
        } else {
          formatRoleDates(vm.edit_data.new_role_id, {
            start: areDatesCustom ? vm.edit_data.new_role_id.newStartDate : contract.start_date,
            end: areDatesCustom ? vm.edit_data.new_role_id.newEndDate : contract.end_date
          },
          {
            start: 'newStartDate',
            end: 'newEndDate'
          });
        }
      };

      /**
       *
       * @param  {Object} event
       */
      vm.open = function (event) {
        vm.CalendarShow[event] = true;
      };

      /**
       * Removes the given Role
       *
       * @param {Object} jobRole
       */
      vm.removeRole = function (jobRole) {
        $log.debug('Remove Role');

        var modalInstance = $modal.open({
          appendTo: $rootElement.find('div').eq(0),
          template: '',
          templateUrl: settings.pathApp+'views/modalDialog.html?v='+(new Date()).getTime(),
          size: 'sm',
          controller: 'ModalDialogCtrl',
          resolve: {
            content: function(){
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

              return getJobRolesList(vm.contactId);
            });
          }
        })
      };

      /**
       * Validates Dates and saves the new Job Role
       */
      vm.saveNewRole = function () {
        var newRole;

        $log.debug('Add New Role');

        vm.errors = {};
        vm.errors.newStartDate = [];
        vm.errors.newEndDate = [];

        var contract = getContractData(vm.edit_data.new_role_id.job_contract_id);
        var validateResponse = validateDates({
          'start': vm.edit_data.new_role_id.newStartDate,
          'end': vm.edit_data.new_role_id.newEndDate,
          'contractStart': contract.start_date,
          'contractEnd': contract.end_date,
        },
        {
          'start': vm.errors.newStartDate,
          'end': vm.errors.newEndDate
        });

        if (validateResponse) {
          var newRole = angular.copy(vm.edit_data.new_role_id);
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

            // Hide the add new form
            vm.add_new = false;

            // Remove if any data are added / Reset form
            delete vm.edit_data['new_role_id'];

            return getJobRolesList(vm.contactId);
          });
        }
      };

      /**
       *
       * @param  {Object} event
       */
      vm.select = function (event) {
        vm.CalendarShow[event] = false;
      };

       /**
       * Show Row Type default value
       *
       * @param object
       * @returns {string}
       */
      vm.showRowType = function (object) {
        var selected = '';

        if (typeof object.type !== "undefined") {
          // Get the human readable Type Value
          selected = vm.rowTypes[object.type];

          return selected.name;
        }

        return 'Not set';
      };

      /**
       * Set the is_edit value
       *
       * @param {int} row_id
       */
      vm.showSave = function (row_id) {
        vm.edit_data[row_id]['is_edit'] = true;
      };

      /**
       *
       */
      vm.today = function () {
        vm.CalendarShow['newStartDate'] = false;
        vm.CalendarShow['newEndDate'] = false;
        vm.CalendarShow['start_date'] = false;
        vm.CalendarShow['end_date'] = false;
      };

      /**
       * Update funder type scope on request
       *
       * @param  {int} role_id
       * @param  {string} row_type
       * @param  {string} key
       * @param  {*} data
       */
      vm.updateAdditionalRowType = function (role_id, row_type, key, data) {
        if (row_type === 'cost_centre') {
          // Update cost centers row
          vm.edit_data[role_id]['cost_centers'][key]['type'] = data;
        } else {
          // Update funder Type scope as default
          vm.edit_data[role_id]['funders'][key]['type'] = data;
        }
      };

      /**
       * Prepares data and updates existing role
       *
       * @param {int} role_id
       * @param {string} role_type
       */
      vm.updateRole = function (role_id, role_type) {
        var updatedRole;

        $log.debug('Update Role');

        if (typeof role_type === 'string') {
          filterEmptyData(role_id, role_type);
        }

        updatedRole = angular.copy(vm.edit_data[role_id]);
        updatedRole.location = (updatedRole.location === undefined)? updatedRole.location = '' : updatedRole.location;
        updatedRole.level = (updatedRole.level === undefined)? updatedRole.level = '' : updatedRole.level;
        updatedRole.department = (updatedRole.department === undefined)? updatedRole.department = '' : updatedRole.department;
        updatedRole.region = (updatedRole.region === undefined)? updatedRole.region = '' : updatedRole.region;
        updatedRole.start_date = convertDateToServerFormat(updatedRole.start_date);

        if (updatedRole.end_date) {
          updatedRole.end_date = convertDateToServerFormat(updatedRole.end_date);
        } else {
          delete updatedRole.end_date;
        }

        if (updatedRole.funders && updatedRole.funders.length) {
          updateFundersContactsList(updatedRole.funders);
        }

        updateJobRole(role_id, updatedRole).then(function () {
          updateHeaderInfo(updatedRole);

          return getJobRolesList(vm.contactId);
        });
      };

      /**
       * Validation method for JobRole data.
       * If string is returned form is not submitted.
       *
       * @param {Object} data
       * @return {boolean|string}
       */
      vm.validateRole = function (data) {
        // Reset Error Messages
        data.start_date.$error.custom = [];
        data.end_date.$error.custom = [];
        var contract = getContractData(data.contract.$viewValue);

        if(contract == undefined){
          return 'Contract is missing';
        }
        var validateResponse = validateDates({
            'start': data.start_date.$viewValue,
            'end': data.end_date.$viewValue,
            'contractStart': contract.start_date,
            'contractEnd': contract.end_date,
          },
          {
            'start': data.start_date.$error.custom,
            'end': data.end_date.$error.custom
          });

        return (validateResponse ? true : 'Error');
      };

      /**
       *
       * @param {string} title
       * @returns {string|undefined}
       */
      vm.validateTitle = function (title) {
        if (title === 'title' || title === ' ') {
          return "Title cannot be title!";
        }
      };

      /**
       * Get the contact list and store the data
       *
       * @param  {string} sortName
       */
      vm.getContactList = function (sortName) {
        var successCallback = function (data) {
          var contactList = [], i = 0;

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

        return HRJobRolesService.getContactList(sortName).then(successCallback, errorCallback);
      };


      // Init block
      (function init() {
        vm.today();

        $q.all([
          getOptionValues(),
          getJobRolesList(vm.contactId),
          vm.getContactList()
        ])
        .then(function () {
          vm.loading = false;
        });
      })();


      /**
       * Implements the "createJobRole" service
       *
       * @param  {Object} job_roles_data
       * @return {Promise}
       */
      function createJobRole(job_roles_data) {
        return HRJobRolesService.createJobRole(job_roles_data).then(function (data) {
            return data;
          }, function (errorMessage) {
            vm.error = errorMessage;
          });
      }

      /**
       * Fetches the contract ids of the given contact
       *
       * @param {int} contactId
       * @return {Promise} resolves with an array of contract ids
       */
      function contractIdsFromContact(contactId) {
        return HRJobRolesService.getContracts(contactId).then(function (data) {
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
              status: status,
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
      function convertDateToServerFormat(date) {
        var dateString = formatDate(date, 'YYYY-MM-DD');

        return dateString !== 'Unspecified' ? dateString : null;
      }

      /**
       * Implements the "deleteJobRole" service
       *
       * @param  {int} job_role_id
       * @return {Promise}
       */
      function deleteJobRole(job_role_id) {
        return HRJobRolesService.deleteJobRole(job_role_id).then(function (data) {
            return data;
          },
          function (errorMessage) {
            vm.error = errorMessage;
          });
      }

      /**
       * Extracts, from each job role past and preset, the contact id of every funder
       *
       * It combines present and past job roles, make a list of the funder ids string,
       * splits it by the "|" separator, and return only the non-null and unique values
       *
       * @return {Array} a list of ids
       */
      function extractFundersContactIds() {
        return _(vm.present_job_roles.concat(vm.past_job_roles))
          .map(function (jobRole) {
            return jobRole.funder;
          })
          .thru(function (funderIds) {
            return funderIds.join('').split('|');
          })
          .compact().uniq().value();
      }

      /**
       * Get a contract with the given contractId
       *
       * @param {int} contractId
       * @returns {object}
       */
      function getContractData(contractId) {
        return vm.contractsData[contractId];
      }

      /**
       * Filter the edit_data property to remove
       * the funders/cost_centers entries which are empty
       *
       * @param  {string|int} role_id
       * @param  {string} role_type
       */
      function filterEmptyData(role_id, role_type) {
        if (vm.edit_data.hasOwnProperty(role_id)) {
          if (role_type === 'funders') {
            vm.edit_data[role_id][role_type] = HRJobRolesServiceFilters.issetFunder(vm.edit_data[role_id][role_type]);
          }

          if (role_type === 'cost_centers') {
            vm.edit_data[role_id][role_type] = HRJobRolesServiceFilters.issetCostCentre(vm.edit_data[role_id][role_type]);
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
      function formatRoleDates(role, dates, keys) {
        var keys = keys ? keys : { start: 'start_date', end: 'end_date' };

        role[keys.start] = !!dates.start ? formatDate(dates.start, Date) : null;
        role[keys.end]   = !!dates.end   ? formatDate(dates.end, Date)   : null;
      }

      /**
       * Fetches from the API the contact data of all the funders of each job role
       *
       * @return {Promise}
       *   resolves to an object, with the key as the contact id,
       *   and the value as the contact data
       */
      function getFundersContacts() {
        return HRJobRolesService.getContactList(null, extractFundersContactIds()).then(function (data) {
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
      function getJobRolesList(contactId) {
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
      function getOptionValues() {
        // Set the option groups for which we want to get the values
        var option_groups = ['hrjc_department', 'hrjc_region', 'hrjc_location', 'hrjc_level_type', 'cost_centres'];

        return HRJobRolesService.getOptionValues(option_groups).then(function (data) {

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

              angular.forEach(data['optionGroupData'], function (option_group_id, option_group_name) {

                for (var i = 0; i < data.count; i++) {

                  switch (option_group_name) {
                    case 'hrjc_department':

                      if (option_group_id === data.values[i]['option_group_id']) {
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

                      if (option_group_id === data.values[i]['option_group_id']) {
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

                      if (option_group_id === data.values[i]['option_group_id']) {
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

                      if (option_group_id === data.values[i]['option_group_id']) {
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
                      if (option_group_id === data.values[i]['option_group_id']) {
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
      function initCostCentersData(jobRole, data) {
        jobRole.cost_centers = [];

        var cost_center_contact_ids = HRJobRolesServiceFilters.isNotUndefined(data.cost_center.split('|')),
            cost_center_types = data.cost_center_val_type.split('|'),
            percent_cost_centers = data.percent_pay_cost_center.split('|'),
            amount_cost_centers = data.amount_pay_cost_center.split('|');

        for (var i = 0; i < cost_center_contact_ids.length; i++) {
          if (cost_center_contact_ids[i] !== '') {
            jobRole.cost_centers.push({
              id: jobRole.cost_centers.length + 1,
              amount: amount_cost_centers[i],
              cost_centre_id: cost_center_contact_ids[i],
              percentage: percent_cost_centers[i],
              type: cost_center_types[i]
            });
          }
        }
      }

      /**
       * Initializes the funders data in the given job role
       *
       * @param  {Object} jobRole
       * @param  {Object} data
       */
      function initFundersData(jobRole, data) {
        jobRole.funders = [];

        var funder_contact_ids = HRJobRolesServiceFilters.isNotUndefined(data.funder.split('|')),
            funder_types = data.funder_val_type.split('|'),
            percent_funders = data.percent_pay_funder.split('|'),
            amount_funders = data.amount_pay_funder.split('|');

        for (var i = 0; i < funder_contact_ids.length; i++) {
          if (funder_contact_ids[i] !== '') {
            jobRole.funders.push({
              id: jobRole.funders.length + 1,
              amount: amount_funders[i],
              percentage: percent_funders[i],
              type: funder_types[i],
              funder_id: {
                id: funder_contact_ids[i],
                sort_name: fundersContacts[funder_contact_ids[i]].sort_name
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
      function initMiscData(jobRole, key, data) {
        var bothJustSet = (typeof jobRole.start_date === 'undefined'
        || typeof jobRole.job_contract_id === 'undefined');

        // Default data init
        jobRole[key] = data;

        if (!!jobRole.start_date) {
          // If dates are not set, we programatically set them here
          var date = moment(jobRole.start_date);
          var invalidDate = (isNaN(date) && typeof jobRole.start_date !== 'undefined');

          var presentJobContract = !(typeof jobRole.job_contract_id === 'undefined');

          if (invalidDate && presentJobContract && bothJustSet) {
            vm.onContractEdited(null, role_id).then(function () {
              $scope.$apply();
              return vm.updateRole(role_id);
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
       * Checks if date should be considered empty.
       *
       * @param {String} date
       * @returns {boolean}
       */
      function isDateEmpty(date) {
        return date === null;
      }

      /**
       * Fetches the job roles of the contracts with the given ids
       *
       * @param {Array} contractIds
       * @return {Promise}
       */
      function jobRolesFromContracts(contractIds) {
        return HRJobRolesService.getAllJobRoles(contractIds)
          .then(function (data) {
            vm.present_job_roles = [];
            vm.past_job_roles = [];
            vm.status = 'Data load OK';

            if (data.is_error === 1) {
              vm.error = 'Data load failure';
            }

            data.values.forEach(function (object_data) {
              var todaysDate = moment().startOf('day');
              var endDate = null;

              if(!isDateEmpty(object_data.end_date)) {
                endDate = moment(object_data.end_date).startOf('day');
              }

              if (!endDate || endDate.isSameOrAfter(todaysDate)) {
                vm.present_job_roles.push(object_data);
              } else {
                vm.past_job_roles.push(object_data);
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
       * Updates the internal list of funders contacts with the funders list
       * of the given job role. If the job role has any funder which is not
       * already stored in the list, the funder gets added
       *
       * @param  {Array} jobRoleFunders
       */
      function updateFundersContactsList(jobRoleFunders) {
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
      function updateHeaderInfo(jobRole) {
        if (vm.contractsData[jobRole.job_contract_id].is_current) {
          HRJobRolesService.getCurrentDepartments(jobRole.job_contract_id).then(function (departments) {
            DOMEventTrigger('updateContactHeader', {
              roles: { departments: departments }
            });
          });
        }
      }

      /**
       * Implements the "updateJobRole" service
       *
       * @param  {int} role_id
       * @param  {Object} job_roles_data
       * @return {Promise}
       */
      function updateJobRole(role_id, job_roles_data) {
        return HRJobRolesService.updateJobRole(role_id, job_roles_data).then(function (data) {
          return data;
        }, function (errorMessage) {
          vm.error = errorMessage;
        });
      }

      /**
       * Checks if JobRole dates are actual, if not checks if they exist in any of contract's revisions.
       *
       * @param {int} role_id
       */
      function updateRolesWithContractData(role_id) {
        var contract_id = vm.edit_data[role_id].job_contract_id;

        if (vm.checkIfDatesAreCustom(vm.edit_data[role_id]['start_date'], vm.edit_data[role_id]['end_date'])) {
          var contract = getContractData(contract_id);

          // search for revision containing these dates
          var revision = contract.revisions.some(function (rev) {
            return rev.period_start_date === formatDate(vm.edit_data[role_id]['start_date'])
              && rev.period_end_date === formatDate(vm.edit_data[role_id]['end_date']);
          });

          // check if dates match with revision
          if (revision) {
            formatRoleDates(vm.edit_data[role_id], {
              start: contract.start_date,
              end: contract.end_date
            });

            vm.updateRole(role_id);
          }
        } else {
          formatRoleDates(vm.edit_data[role_id], {
            start: vm.edit_data[role_id].start_date,
            end: vm.edit_data[role_id].end_date
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
      function validateDates(data, errors) {
        var errorsCount = 0;

        DateValidation.setErrorCallback(function (error, field) {
          errorsCount++;
          if (field.indexOf('start_date') > -1) {
            errors.start.push(error);
          }
          if (field.indexOf('end_date') > -1) {
            errors.end.push(error);
          }
        });
        DateValidation.validate(data.start, data.end, data.contractStart, data.contractEnd);

        return (errorsCount === 0);
      }

      // PubSub Events
      // Triggers when a new contract is created for a contact.
      pubSub.subscribe('contract:created', function(contactId){
        contractIdsFromContact(contactId);
      });

      // Triggers when a contract is deleted for a contact.
      pubSub.subscribe('contract:deleted', function(data) {
        contractIdsFromContact(data.contactId).then(function(contractIds) {
          if(!contractIds.length){
            vm.present_job_roles = [];
            vm.past_job_roles = [];
          } else {
            return jobRolesFromContracts(contractIds);
          }
        });
      });
    }
  ]);
});
