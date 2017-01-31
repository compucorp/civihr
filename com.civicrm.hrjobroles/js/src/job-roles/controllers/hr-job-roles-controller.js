define([
  'common/angular',
  'job-roles/controllers/controllers',
  'common/moment',
  'common/lodash',
  'common/filters/angular-date/format-date'
], function (angular, controllers, moment, _) {
  'use strict';

  controllers.controller('HRJobRolesController', [
    '$scope', '$log', '$routeParams', '$route', '$uibModal', '$rootElement', '$timeout', '$filter', '$q','settings',
    'HR_settings', 'HRJobRolesService', 'DateValidation', 'HRJobRolesServiceFilters',
    'DOMEventTrigger',
    function ($scope, $log, $routeParams, $route, $modal, $rootElement, $timeout, $filter, $q, settings, HR_settings, HRJobRolesService, DateValidation, HRJobRolesServiceFilters, DOMEventTrigger) {
      $log.debug('Controller: HRJobRolesController');

      $scope.format = HR_settings.DATE_FORMAT;

      var me = this;
      var formatDate = $filter('formatDate');
      var roles_type = ['funders', 'cost_centers'];

      $scope.present_job_roles = [];
      $scope.past_job_roles = [];
      $scope.job_roles_loaded = false;

      $scope.dpOpen = function ($event) {
        $event.preventDefault();
        $event.stopPropagation();

        $scope.picker.opened = true;
      };

      /**
       * Checks if date should be considered empty.
       *
       * @param {String} date
       * @returns {boolean}
       */
      var isDateEmpty = function(date){
        return date === null;
      };

      /**
       * Method responsible for updating new JobRole with dates from Contract
       */
      $scope.onContractSelected = function () {
        var contract = getContractData($scope.edit_data.new_role_id.job_contract_id);
        var areDatesCustom = $scope.checkIfDatesAreCustom($scope.edit_data.new_role_id.newStartDate, $scope.edit_data.new_role_id.newEndDate);

        formatRoleDates($scope.edit_data.new_role_id, {
            start: areDatesCustom ? $scope.edit_data.new_role_id.newStartDate : contract.start_date,
            end: areDatesCustom ? $scope.edit_data.new_role_id.newEndDate : contract.end_date
          },
          {
            start: 'newStartDate',
            end: 'newEndDate'
          });
      };

      /**
       * Checks if dates don't exist in any of contracts
       * @param start
       * @param end
       * @returns {boolean}
       */
      $scope.checkIfDatesAreCustom = function (start, end) {
        if (isDateEmpty(start)) start = null;
        if (isDateEmpty(end)) end = null;

        var custom = true;

        if (!start) return false;


        angular.forEach(me.contractsData, function (value) {
          if (formatDate(start) === formatDate(value.start_date)
            && formatDate(end) === formatDate(value.end_date))
            custom = false;
        });

        return custom;
      };

      /**
       * Method responsible for updating existing JobRole with dates from Contract
       * @param jobContractId
       * @param role_id
       */
      $scope.onContractEdited = function (jobContractId, role_id) {
        var id = jobContractId || $scope.edit_data[role_id]['job_contract_id'];
        var contract = getContractData(id);
        var areDatesCustom = $scope.checkIfDatesAreCustom($scope.edit_data[role_id]['start_date'], $scope.edit_data[role_id]['end_date']);

        if (!areDatesCustom) {
          formatRoleDates($scope.edit_data[role_id], {
            start: contract.start_date,
            end: contract.end_date
          });
        } else {
          formatRoleDates($scope.edit_data[role_id], {
            start: $scope.edit_data[role_id].start_date,
            end: $scope.edit_data[role_id].end_date
          });
        }
      };

      /**
       *
       * @param title
       * @returns {string|undefined}
       */
      $scope.validateTitle = function (title) {
        if (title === 'title' || title === ' ') {
          return "Title cannot be title!";
        }
      };

      /**
       * Validation method for JobRole data.
       * If string is returned form is not submitted.
       * @param data
       * @returns {boolean|string}
       */
      $scope.validateRole = function (data) {
        // Reset Error Messages
        data.start_date.$error.custom = [];
        data.end_date.$error.custom = [];

        var contract = getContractData(data.contract.$viewValue);

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

      $scope.today = function () {
        $scope.CalendarShow['newStartDate'] = false;
        $scope.CalendarShow['newEndDate'] = false;
        $scope.CalendarShow['start_date'] = false;
        $scope.CalendarShow['end_date'] = false;
      };

      $scope.isOpen = function (name) {
        return !!($scope.CalendarShow[name]);
      };

      // As default hide the datepickers
      $scope.CalendarShow = [];

      // Init values
      $scope.today();

      $scope.open = function (event) {
        $scope.CalendarShow[event] = true;
      };

      $scope.select = function (event) {
        $scope.CalendarShow[event] = false;
      };

      // Tracks collapsed / expanded rows
      $scope.collapsedRows = [];

      // Tracks clicked tabs per each row
      $scope.view_tab = [];

      // Tracks edit data changes on the forms
      $scope.edit_data = {};

      // Define the add new role URL
      $scope.add_new_role_url = $scope.$parent.pathBaseUrl + $scope.$parent.pathIncludeTpl + 'add_new_role.html';
      $scope.job_role_panel_url = $scope.$parent.pathBaseUrl + $scope.$parent.pathIncludeTpl + 'job_role_panel.html';

      // Store the contractsData
      $scope.contractsData = [];

      // Store the level types
      $scope.LevelsData = {};

      // Store the location types
      $scope.LocationsData = {};

      // Store the region types
      $scope.RegionsData = {};

      // Store the department types
      $scope.DepartmentsData = {};

      // Contact List IDs array to use for the select lists
      $scope.contactList = [];

      // Contact List object stores more details about the contact
      $scope.contactListObject = {};

      // Implement angular tabs
      $scope.changeTab = function (row_id, tab_id) {
        $scope.view_tab[row_id] = tab_id;
      };

      // Check if current tab
      $scope.isTab = function (row_id, tab_id) {
        return ($scope.view_tab[row_id] == tab_id);
      };

      // Check for collapsed rows
      $scope.isRowCollapsed = function (row_id) {
        return !!($scope.collapsedRows[row_id]);
      };

      // Collapse the row or Expand when clicked
      $scope.collapseRow = function (row_id) {
        $scope.collapsedRows[row_id] = !$scope.collapsedRows[row_id];
      };

      // Set the data from the webservice call
      $scope.initData = function (role_id, form_id, data) {

        // Check if we have the array already
        if (typeof $scope.edit_data[role_id] === "undefined") {
          $scope.edit_data[role_id] = {};
        }
        // If we have funders or cost centers, we have a special way to init our data
        if (form_id === 'funders') {

          // Init empty array for funder default values
          $scope.edit_data[role_id]['funders'] = [];

          // Split data from the stored funder contact IDs
          var funder_contact_ids = HRJobRolesServiceFilters.isNotUndefined(data['funder'].split("|"));

          // Split the funder types
          var funder_types = data['funder_val_type'].split("|");

          // Split the percent value for the funder
          var percent_funders = data['percent_pay_funder'].split("|");

          // Split the amount value for the funder
          var amount_funders = data['amount_pay_funder'].split("|");

          // Loop data and crete the required array of values
          for (var i = 0; i < funder_contact_ids.length; i++) {
            if (funder_contact_ids[i] != "") {
              // Set default funder rows funder rows
              $scope.edit_data[role_id]['funders'].push({
                id: $scope.edit_data[role_id]['funders'].length + 1,
                funder_id: {
                  id: funder_contact_ids[i],
                  sort_name: job_roles.contactListObject[funder_contact_ids[i]]['sort_name']
                },
                type: funder_types[i],
                percentage: percent_funders[i],
                amount: amount_funders[i]
              });
            }
          }
        }
        // If we have funders or cost centers, we have a special way to init our data
        else if (form_id === 'cost_centers') {
          // Init empty array for funder default values
          $scope.edit_data[role_id]['cost_centers'] = [];

          // Split data from the stored funder contact IDs
          var cost_center_contact_ids = HRJobRolesServiceFilters.isNotUndefined(data['cost_center'].split("|"));

          // Split the cost_centers types
          var cost_center_types = data['cost_center_val_type'].split("|");

          // Split the percent value for the cost_center
          var percent_cost_centers = data['percent_pay_cost_center'].split("|");

          // Split the amount value for the cost_center
          var amount_cost_centers = data['amount_pay_cost_center'].split("|");

          // Loop data and crete the required array of values
          for (var i = 0; i < cost_center_contact_ids.length; i++) {

            if (cost_center_contact_ids[i] != "") {

              // Set default funder rows funder rows
              $scope.edit_data[role_id]['cost_centers'].push({
                id: $scope.edit_data[role_id]['cost_centers'].length + 1,
                cost_centre_id: cost_center_contact_ids[i],
                type: cost_center_types[i],
                percentage: percent_cost_centers[i],
                amount: amount_cost_centers[i]
              });

            }

          }

        } else {

          var bothJustSet = (typeof $scope.edit_data[role_id].start_date === 'undefined'
          || typeof $scope.edit_data[role_id].job_contract_id === 'undefined');

          // Default data init
          $scope.edit_data[role_id][form_id] = data;

          if (!!$scope.edit_data[role_id].start_date) {
            var date = moment($scope.edit_data[role_id].start_date);
            /* If dates are not set, we programatically set them here. */
            var invalidDate = (isNaN(date) && typeof $scope.edit_data[role_id].start_date != 'undefined');

            var presentJobContract = !(typeof $scope.edit_data[role_id].job_contract_id === 'undefined');

            if (invalidDate && presentJobContract && bothJustSet) {
              $scope.onContractEdited(null, role_id).then(function () {
                $scope.$apply();
                return $scope.updateRole(role_id);
              });
            } else {
              formatRoleDates($scope.edit_data[role_id], {
                start: $scope.edit_data[role_id].start_date,
                end: $scope.edit_data[role_id].end_date
              });
            }
          }
        }

        if (form_id === 'end_date' && !$scope.edit_data[role_id].end_date) {
          $scope.edit_data[role_id].end_date = null;
        }

        if ($scope.edit_data[role_id].job_contract_id
          && $scope.edit_data[role_id].start_date
          && typeof $scope.edit_data[role_id].end_date != 'undefined'
          && (form_id === 'start_date' || form_id === 'job_contract_id' || form_id === 'end_date')) {

          updateRolesWithContractData(role_id);
        }
      };

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
       * Checks if JobRole dates are actual, if not checks if they exist in any of contract's revisions.
       * @param role_id
       */
      function updateRolesWithContractData(role_id) {
        var contract_id = $scope.edit_data[role_id].job_contract_id;

        if ($scope.checkIfDatesAreCustom($scope.edit_data[role_id]['start_date'], $scope.edit_data[role_id]['end_date'])) {
          var contract = getContractData(contract_id);

          // search for revision containing these dates
          var revision = contract.revisions.some(function (rev) {
            return rev.period_start_date === formatDate($scope.edit_data[role_id]['start_date'])
              && rev.period_end_date === formatDate($scope.edit_data[role_id]['end_date']);
          });

          // check if dates match with revision
          if (revision) {
            formatRoleDates($scope.edit_data[role_id], {
              start: contract.start_date,
              end: contract.end_date
            });

            $scope.updateRole(role_id);
          }
        } else {
          formatRoleDates($scope.edit_data[role_id], {
            start: $scope.edit_data[role_id].start_date,
            end: $scope.edit_data[role_id].end_date
          });
        }
      }

      /**
       * Check if the data are changed in the form (based on job role ID)
       * @param row_id
       * @returns {boolean}
       */
      $scope.isChanged = function (row_id) {
        // If there are data it means we edited the form
        return !!($scope.edit_data[row_id]['is_edit']);
      };

      /**
       * Set the is_edit value
       * @param row_id
       */
      $scope.showSave = function (row_id) {
        $scope.edit_data[row_id]['is_edit'] = true;
      };

      /**
       * Check if we allow to submit the form
       * Rule -> Allow only if the minimum required data are filled
       * @returns {boolean}
       */
      $scope.checkNewRole = function checkNewRole() {

        return (typeof $scope.edit_data['new_role_id'] === 'undefined'
        || typeof $scope.edit_data['new_role_id']['title'] === 'undefined'
        || $scope.edit_data['new_role_id']['title'] === ''
        || typeof $scope.edit_data['new_role_id']['job_contract_id'] === 'undefined'
        || $scope.edit_data['new_role_id']['job_contract_id'] === '');
      };

      /**
       * Validates Dates and saves the new Job Role
       */
      $scope.saveNewRole = function saveNewRole() {
        var newRole;

        $log.debug('Add New Role');

        $scope.errors = {};
        $scope.errors.newStartDate = [];
        $scope.errors.newEndDate = [];

        var contract = getContractData($scope.edit_data.new_role_id.job_contract_id);
        var validateResponse = validateDates({
          'start': $scope.edit_data.new_role_id.newStartDate,
          'end': $scope.edit_data.new_role_id.newEndDate,
          'contractStart': contract.start_date,
          'contractEnd': contract.end_date,
        },
        {
          'start': $scope.errors.newStartDate,
          'end': $scope.errors.newEndDate
        });

        if (validateResponse) {
          newRole = angular.copy($scope.edit_data.new_role_id);

          newRole.newStartDate = convertDateToServerFormat(newRole.newStartDate);

          if (newRole.newEndDate) {
            newRole.newEndDate = convertDateToServerFormat(newRole.newEndDate);
          } else {
            delete newRole.newEndDate;
          }

          // Create the new job role
          createJobRole(newRole).then(function () {
            updateHeaderInfo(newRole);

            // Hide the add new form
            $scope.add_new = false;

            // Remove if any data are added / Reset form
            delete $scope.edit_data['new_role_id'];

            return getJobRolesList($scope.$parent.contactId);
          });
        }
      };

      /**
       * Sets the add new job role form visibility
       */
      $scope.add_new_role = function () {
        $scope.add_new = true;
      };

      /**
       * Hides the add new job role form and removes any data added.
       */
      $scope.cancelNewRole = function () {
        $scope.add_new = false;
        delete $scope.edit_data['new_role_id'];
      };

      /**
       * Removes the given Role
       * @param {object} jobRole
       */
      $scope.removeRole = function (jobRole) {
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

              return getJobRolesList($scope.$parent.contactId);
            });
          }
        })
      };

      /**
       * Prepares data and updates existing role
       * @param role_id
       */
      $scope.updateRole = function (role_id, role_type) {
        var updatedRole;

        $log.debug('Update Role');

        if (typeof role_type === 'string') {
          filterEmptyData(role_id, role_type);
        }

        updatedRole = angular.copy($scope.edit_data[role_id]);
        updatedRole.start_date = convertDateToServerFormat(updatedRole.start_date);

        if (updatedRole.end_date) {
          updatedRole.end_date = convertDateToServerFormat(updatedRole.end_date);
        } else {
          delete updatedRole.end_date;
        }

        // Update the job role
        updateJobRole(role_id, updatedRole).then(function () {
          updateHeaderInfo(updatedRole);

          return getJobRolesList($scope.$parent.contactId);
        });
      };

      // Select list for Row Types (used for Funders and Cost Centers)
      $scope.rowTypes = {};
      $scope.rowTypes[0] = { id: 0, name: 'Fixed' };
      $scope.rowTypes[1] = { id: 1, name: '%' };

      //$scope.rowTypes = [ {id: 0, name: 'Fixed'}, {id: 1, name: '%'}];

      /**
       * Show Row Type default value
       * @param object
       * @returns {string}
       */
      $scope.showRowType = function (object) {
        var selected = '';

        if (typeof object.type !== "undefined") {

          // Get the human readable Type Value
          selected = $scope.rowTypes[object.type];

          return selected.name;
        }
        return 'Not set';
      };

      $scope.getCostLabel = function (id) {
        var label = '';
        angular.forEach($scope.CostCentreList, function (v, k) {
          if (v.id == id) {
            label = v.title;
          }
        });

        return label;
      };

      // Update funder type scope on request
      $scope.updateAdditionalRowType = function (role_id, row_type, key, data) {
        if (row_type === 'cost_centre') {
          // Update cost centers row
          $scope.edit_data[role_id]['cost_centers'][key]['type'] = data;
        } else {
          // Update funder Type scope as default
          $scope.edit_data[role_id]['funders'][key]['type'] = data;
        }
      };

      // Add additional rows (funder or cost centres)
      $scope.addAdditionalRow = function (role_id, row_type) {

        // Check if we have the array already
        if (typeof $scope.edit_data[role_id] === "undefined") {
          $scope.edit_data[role_id] = {};
        }

        if (row_type === 'cost_centre') {

          // Add cost centres
          // Check if we have the array already
          if (typeof $scope.edit_data[role_id]['cost_centers'] === "undefined" || !($scope.edit_data[role_id]['cost_centers'] instanceof Array)) {
            $scope.edit_data[role_id]['cost_centers'] = [];
          }

          $scope.edit_data[role_id]['cost_centers'].push({
            id: $scope.edit_data[role_id]['cost_centers'].length + 1,
            cost_centre_id: '',
            type: "1",
            percentage: "0",
            amount: "0"
          });

        } else {

          // As default add funder rows
          // Check if we have the array already
          if (typeof $scope.edit_data[role_id]['funders'] === "undefined" || !($scope.edit_data[role_id]['funders'] instanceof Array)) {
            $scope.edit_data[role_id]['funders'] = [];
          }

          $scope.edit_data[role_id]['funders'].push({
            id: $scope.edit_data[role_id]['funders'].length + 1,
            funder_id: '',
            type: "1",
            percentage: "0",
            amount: "0"
          });
        }
      };

      // Delete Additional rows (funder or cost centres)
      $scope.deleteAdditionalRow = function (role_id, row_type, row_id) {
        if (row_type === 'cost_centre') {
          // Remove the cost centre row
          $scope.edit_data[role_id]['cost_centers'].splice(row_id, 1);
        } else {
          // Remove the funder row as default
          $scope.edit_data[role_id]['funders'].splice(row_id, 1);
        }
      };

      /**
       * Called on angular-xeditable's onaftersave callback.
       * It'll filter the rows which are without data.
       *
       * @param  {string|int} role_id
       * @param  {string} role_type
       */
      $scope.onAfterSave = function (role_id, role_type) {
        filterEmptyData(role_id, role_type);
      }

      /**
       * Called on angular-xeditable's cancel callback.
       * It'll filter the rows which are without data.
       *
       * @param  {string|int} role_id
       * @param  {string} role_type
       */
      $scope.onCancel = function (role_id, role_type) {
        if (role_type === 'both') {
          roles_type.map(function (type) {
            filterEmptyData(role_id, type);
          });
        } else {
          filterEmptyData(role_id, role_type);
        }
      }

      /**
       * Filter the edit_data property to remove
       * the funders/cost_centers entries which are empty
       *
       * @param  {string|int} role_id
       * @param  {string} role_type
       */
      function filterEmptyData(role_id, role_type) {
        if ($scope.edit_data.hasOwnProperty(role_id)) {
          if (role_type === 'funders') {
            $scope.edit_data[role_id][role_type] = HRJobRolesServiceFilters.issetFunder($scope.edit_data[role_id][role_type]);
          }

          if (role_type === 'cost_centers') {
            $scope.edit_data[role_id][role_type] = HRJobRolesServiceFilters.issetCostCentre($scope.edit_data[role_id][role_type]);
          }
        }
      }

      // Variable to check if we adding new job role
      var job_roles = this;

      // Get the option groups and option values
      getOptionValues();

      // Get job roles based on the passed Contact ID
      getJobRolesList($scope.$parent.contactId);

      // Get the contact list and store the data

      job_roles.getContactList = function(sortName) {
        var successCallback = function (data) {

          var contact,
            contactList = [],
            contactListObject = {},
            i = 0;

          if (data.is_error === 1) {
            job_roles.message_type = 'alert-danger';
            job_roles.message = 'Cannot get contact list!';
          }
          else {
            // Pass the contact list to the scope
            for (; i < data.count; i++) {
              // Build the contact list
              contact = {
                id: data.values[i]['id'],
                sort_name: data.values[i]['sort_name']
              };
              contactList.push(contact);
              contactListObject[data.values[i]['id']] = contact;
            }
            // Store the ContactList as Array as typeahead needs array what we can reuse later
            job_roles.contactList = contactList;
            // Store the object too, so we can point to right values by Contact ID
            job_roles.contactListObject = contactListObject;
          }

          // Hide the message after some seconds
          $timeout(function () {
            job_roles.message = null;
          }, 3000);
        };
        var errorCallback = function (errorMessage) {
          $scope.error = errorMessage;
        };

        HRJobRolesService.getContactList(sortName).then(successCallback,errorCallback);
      };
      job_roles.getContactList();

      function getOptionValues() {

        // Set the option groups for which we want to get the values
        var option_groups = ['hrjc_department', 'hrjc_region', 'hrjc_location', 'hrjc_level_type', 'cost_centres'];

        HRJobRolesService.getOptionValues(option_groups).then(function (data) {

            if (data.is_error === 1) {
              job_roles.message_type = 'alert-danger';
              job_roles.message = 'Cannot get option values!';
            }
            else {

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
                          weight: data.values[i]['weight'],
                          is_active: data.values[i]['is_active']
                        });
                      }

                      break;
                  }


                }

              });

              // Store the Department types what we can reuse later
              job_roles.DepartmentsData = DepartmentList;

              // Store the Region types what we can reuse later
              job_roles.RegionsData = RegionList;

              // Store the Location types what we can reuse later
              job_roles.LocationsData = LocationList;

              // Store the Level types what we can reuse later
              job_roles.LevelsData = LevelList;

              // Store the Level types what we can reuse later
              $scope.CostCentreList = CostCentreList;

              job_roles.message_type = 'alert-success';
              job_roles.message = null;
            }

            // Hide the message after some seconds
            $timeout(function () {
              job_roles.message = null;
            }, 3000);
          },
          function (errorMessage) {
            $scope.error = errorMessage;
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
          job_roles.contractsData = contractsData;


          job_roles.job_contract_ids = jobContractIds;

          return jobContractIds;
        }, function (errorMessage) {
          $scope.error = errorMessage;
        });
      }

      /**
       * Fetches the job roles of the contracts with the given ids
       *
       * @param {Array} contractIds
       * @return {Promise}
       */
      function jobRolesFromContracts(contractIds) {
        return HRJobRolesService.getAllJobRoles(contractIds).then(function (data) {

          job_roles.present_job_roles = [];
          job_roles.past_job_roles = [];
          $scope.job_roles_loaded = true;

          data.values.forEach(function (object_data) {
            var todaysDate = moment().startOf('day');
            var endDate = null;

            if(!isDateEmpty(object_data.end_date)) {
              endDate = moment(object_data.end_date).startOf('day');
            }

            if (!endDate || endDate.isSameOrAfter(todaysDate)) {
              job_roles.present_job_roles.push(object_data);
            } else {
              job_roles.past_job_roles.push(object_data);
            }
          });

          if (data.is_error === 1) {
            job_roles.error = 'Data load failure';
          }

          job_roles.status = 'Data load OK';
        }, function (errorMessage) {
          $scope.error = errorMessage;
        });
      }

      /**
       * Get job roles based on the passed Contact ID (refresh part of the page)
       * @param contactId
       * @returns {promise}
       */
      function getJobRolesList(contactId) {
        var contractsPromise;

        if (!job_roles.job_contract_ids) {
          contractsPromise = contractIdsFromContact(contactId);
        } else {
          contractsPromise = $q.when(job_roles.job_contract_ids);
        }

        contractsPromise.then(function (contractIds) {
          !!contractIds.length && jobRolesFromContracts(contractIds);
        });
      }

      // Implements the "deleteJobRole" service
      function deleteJobRole(job_role_id) {

        return HRJobRolesService.deleteJobRole(job_role_id).then(function (data) {
            if (data.is_error === 1) {
              job_roles.message_type = 'alert-danger';
              job_roles.message = 'Role delete failure!';
            } else {
              job_roles.message_type = 'alert-success';
              job_roles.message = 'Role deleted successfully!';
            }

            // Hide the message after some seconds
            $timeout(function () {
              job_roles.message = null;
            }, 3000);
          },
          function (errorMessage) {
            $scope.error = errorMessage;
          });

      }

      // Implements the "createJobRole" service
      function createJobRole(job_roles_data) {
        return HRJobRolesService.createJobRole(job_roles_data).then(function (data) {
          if (data.is_error === 1) {
            job_roles.message_type = 'alert-danger';
            job_roles.message = 'Role creation failed!';
          } else {
            job_roles.message_type = 'alert-success';
            job_roles.message = 'Role added successfully!';
          }

          // Hide the message after some seconds
          $timeout(function () {
            job_roles.message = null;
          }, 3000);
        }, function (errorMessage) {
          $scope.error = errorMessage;
        });
      }

      // Implements the "updateJobRole" service
      function updateJobRole(role_id, job_roles_data) {
        return HRJobRolesService.updateJobRole(role_id, job_roles_data).then(function (data) {

          if (data.is_error === 1) {
            job_roles.message_type = 'alert-danger';
            job_roles.message = 'Role update failed!';
          }
          else {
            job_roles.message_type = 'alert-success';
            job_roles.message = 'Role updated successfully!';
          }

          // Hide the message after some seconds
          $timeout(function () {
            job_roles.message = null;
          }, 3000);
        }, function (errorMessage) {
          $scope.error = errorMessage;
        });
      }

      /**
       * Trigger validation on JobRole Dates + attach error callback
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

      /**
       * Get a contract with the given contractId
       * @param {int} contractId
       * @returns {object}
       */
      function getContractData(contractId) {
        return me.contractsData[contractId];
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
       * Triggers the update of the contact header via the `hrui` extension
       * by emitting a DOM event with the roles data
       *
       * Given that the header reflects data only related to the current contract,
       * the header update happens only for job roles belonging that particular contract
       */
      function updateHeaderInfo(jobRole) {
        if (job_roles.contractsData[jobRole.job_contract_id].is_current) {
          HRJobRolesService.getCurrentDepartments(jobRole.job_contract_id).then(function (departments) {
            DOMEventTrigger('updateContactHeader', {
              roles: { departments: departments }
            });
          });
        }
      }
    }
  ]);
});
