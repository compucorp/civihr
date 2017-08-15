/* eslint-env amd */

define([
  'common/lodash',
  'job-roles/modules/job-roles.services'
], function (_, services) {
  'use strict';

  services.factory('HRJobRolesService', ['$log', '$q', '$filter', function ($log, $q, $filter) {
    /**
     * Extracts the contract revisions details from the chained api calls
     * properties, then removes the current one and format the dates
     *
     * @param  {Object} contract
     */
    function processContractRevisions (contract) {
      var contractRevisions = contract['api.HRJobContractRevision.get'].values;
      delete (contract['api.HRJobContractRevision.get']);

      contract.revisions = _.compact(contractRevisions
        .map(function (revision) {
          var details = revision['api.HRJobDetails.getsingle'];

          if (details.period_start_date === contract.period_start_date &&
          details.period_end_date === contract.period_end_date) {
            return null;
          }

          details.period_start_date = $filter('formatDate')(details.period_start_date);
          details.period_end_date = $filter('formatDate')(details.period_end_date);

          return details;
        }));
    }

    return {

      /**
       * Gets all contracts and revisions
       *
       * @param {string} contactId
       * @returns {promise}
       */
      getContracts: function (contactId) {
        var deferred = $q.defer();

        CRM.api3('HRJobContract', 'get', {
          'sequential': 1,
          'contact_id': contactId,
          'deleted': 0,
          'return': 'title,period_end_date,period_start_date',
          'api.HRJobContractRevision.get': {
            'jobcontract_id': '$value.id',
            'api.HRJobDetails.getsingle': {
              'jobcontract_revision_id': '$value.id'
            }
          }
        })
        .done(function (contracts) {
          contracts.values.forEach(processContractRevisions);
          deferred.resolve(contracts);
        })
        .error(function () {
          deferred.reject('An error occured while fetching items');
        });

        return deferred.promise;
      },

      getAllJobRoles: function (jobContractIds) {
        var deferred = $q.defer();

        CRM.api3('HrJobRoles', 'get', {
          'sequential': 1,
          'return': 'id,job_contract_id,title,description,status,funder,funder_val_type,percent_pay_funder,amount_pay_funder,cost_center,cost_center_val_type,percent_pay_cost_center,amount_pay_cost_center,level_type,location,region,department,end_date,start_date',
          'job_contract_id': { 'IN': jobContractIds }
        }).done(function (result) {
          // Passing data to deferred's resolve function on successful completion
          deferred.resolve(result);
        }).error(function (result) {
          // Sending a friendly error message in case of failure
          deferred.reject('An error occured while fetching items');
        });

        // Returning the promise object
        return deferred.promise;
      },

      deleteJobRole: function (jobRoleId) {
        var deferred = $q.defer();

        CRM.api3('HrJobRoles', 'delete', {
          'sequential': 1,
          'id': jobRoleId
        }).done(function (result) {
          // Passing data to deferred's resolve function on successful completion
          deferred.resolve(result);
        }).error(function (result) {
          // Sending a friendly error message in case of failure
          deferred.reject('An error occured while deleting items');
        });

        // Returning the promise object
        return deferred.promise;
      },

      createJobRole: function (jobRolesData) {
        // Define funder IDs string
        var funders = '|';

        // Set the funder types
        var funderTypes = '|';

        // Set the percent value for the funder
        var percentFunders = '|';

        // Set the amount value for the funder
        var amountFunders = '|';

        // Define cost_center IDs string
        var costCenters = '|';

        // Set the cost_center types
        var costCenterTypes = '|';

        // Set the percent value for the cost_center
        var percentCostCenters = '|';

        // Set the amount value for the cost_center
        var amountCostCenters = '|';

        // If we have any funders added, loop and save them
        if (typeof jobRolesData.funders !== 'undefined') {
          // Loop funders and set up the data to store the funders
          for (var i = 0, l = jobRolesData.funders.length; i < l; i++) {
            if (jobRolesData.funders[i]) {
              funders += jobRolesData.funders[i]['funder_id']['id'] + '|';
              funderTypes += jobRolesData.funders[i]['type'] + '|';
              percentFunders += jobRolesData.funders[i]['percentage'] + '|';
              amountFunders += jobRolesData.funders[i]['amount'] + '|';
            }
          }
        }

        // If we have any cost_centers added, loop and save them
        if (typeof jobRolesData.cost_centers !== 'undefined') {
          // Loop cost_centers and set up the data to store the cost_centers
          for (i = 0, l = jobRolesData.cost_centers.length; i < l; i++) {
            if (jobRolesData.cost_centers[i]) {
              costCenters += jobRolesData.cost_centers[i]['cost_centre_id'] + '|';
              costCenterTypes += jobRolesData.cost_centers[i]['type'] + '|';
              percentCostCenters += jobRolesData.cost_centers[i]['percentage'] + '|';
              amountCostCenters += jobRolesData.cost_centers[i]['amount'] + '|';
            }
          }
        }

        var deferred = $q.defer();
        // FIXME 'solution' to the bug failing saving correct dates to DB a first save
        this.getNewJobRole(jobRolesData.job_contract_id).then(function (result) {
          return CRM.api3('HrJobRoles', 'update', {
            'id': result.id,
            'sequential': 1,
            'job_contract_id': jobRolesData.job_contract_id,
            'title': jobRolesData.title,
            'description': jobRolesData.description,
            'funder': funders,
            'funder_val_type': funderTypes,
            'percent_pay_funder': percentFunders,
            'amount_pay_funder': amountFunders,
            'cost_center': costCenters,
            'cost_center_val_type': costCenterTypes,
            'percent_pay_cost_center': percentCostCenters,
            'amount_pay_cost_center': amountCostCenters,
            'level_type': jobRolesData.level,
            'location': jobRolesData.location,
            'region': jobRolesData.region,
            'department': jobRolesData.department,
            'start_date': jobRolesData.newStartDate,
            'end_date': jobRolesData.newEndDate || null
          });
        }).then(function (response) {
          // Passing data to deferred's resolve function on successful completion
          deferred.resolve(response);
        }, function (result) {
          // Sending a friendly error message in case of failure
          deferred.reject('An error occured while adding items');
        });

        // Returning the promise object
        return deferred.promise;
      },

      updateJobRole: function (roleId, jobRolesData) {
        // Define funder IDs string
        var funders = '|';

        // Set the funder types
        var funderTypes = '|';

        // Set the percent value for the funder
        var percentFunders = '|';

        // Set the amount value for the funder
        var amountFunders = '|';

        // Define cost_center IDs string
        var costCenters = '|';

        // Set the cost_center types
        var costCenterTypes = '|';

        // Set the percent value for the cost_center
        var percentCostCenters = '|';

        // Set the amount value for the cost_center
        var amountCostCenters = '|';

        // If we have any funders added, loop and save them
        if (typeof jobRolesData.funders !== 'undefined') {
          // Loop funders and set up the data to store the funders
          for (var i = 0, l = jobRolesData.funders.length; i < l; i++) {
            if (jobRolesData.funders[i]) {
              funders += jobRolesData.funders[i]['funder_id']['id'] + '|';
              funderTypes += jobRolesData.funders[i]['type'] + '|';
              percentFunders += jobRolesData.funders[i]['percentage'] + '|';
              amountFunders += jobRolesData.funders[i]['amount'] + '|';
            }
          }
        }

        // If we have any costCenters added, loop and save them
        if (typeof jobRolesData.cost_centers !== 'undefined') {
          // Loop cost_centers and set up the data to store the cost_centers
          for (i = 0, l = jobRolesData.cost_centers.length; i < l; i++) {
            if (jobRolesData.cost_centers[i]) {
              costCenters += jobRolesData.cost_centers[i]['cost_centre_id'] + '|';
              costCenterTypes += jobRolesData.cost_centers[i]['type'] + '|';
              percentCostCenters += jobRolesData.cost_centers[i]['percentage'] + '|';
              amountCostCenters += jobRolesData.cost_centers[i]['amount'] + '|';
            }
          }
        }

        var deferred = $q.defer();

        CRM.api3('HrJobRoles', 'create', {
          'sequential': 1,
          'id': roleId,
          'job_contract_id': jobRolesData.job_contract_id,
          'title': jobRolesData.title,
          'description': jobRolesData.description,
          'status': jobRolesData.status,
          'funder': funders,
          'funder_val_type': funderTypes,
          'percent_pay_funder': percentFunders,
          'amount_pay_funder': amountFunders,
          'cost_center': costCenters,
          'cost_center_val_type': costCenterTypes,
          'percent_pay_cost_center': percentCostCenters,
          'amount_pay_cost_center': amountCostCenters,
          'level_type': jobRolesData.level,
          'location': jobRolesData.location,
          'region': jobRolesData.region,
          'start_date': jobRolesData.start_date,
          'end_date': jobRolesData.end_date || 0,
          'department': jobRolesData.department
        }).done(function (result) {
          // Passing data to deferred's resolve function on successful completion
          deferred.resolve(result);
        }).error(function (result) {
          // Sending a friendly error message in case of failure
          deferred.reject('An error occured while updating items');
        });

        // Returning the promise object
        return deferred.promise;
      },

      getContactList: function (sortName, idsList) {
        var deferred = $q.defer();

        CRM.api3('Contact', 'get', {
          'sequential': 1,
          'return': 'id, sort_name',
          'id': _.isArray(idsList) ? { 'IN': idsList } : null,
          'sort_name': sortName || null
        }).done(function (result) {
          deferred.resolve(result);
        }).error(function (result) {
          deferred.reject('An error occured while fetching items');
        });

        return deferred.promise;
      },

      /**
       * Returns the option values of the given option groups
       *
       * In addition to the standard CiviCRM api response, a property
       * called `optionGroupData` is attached, containing a list
       * of group name/group id pairs
       *
       * @param  {Array} groupNames
       * @return {Promise}
       */
      getOptionValues: function (groupNames) {
        var deferred = $q.defer();

        CRM.api3('OptionValue', 'get', {
          'sequential': 1,
          'option_group_id.name': { 'IN': groupNames },
          'return': [ 'id', 'label', 'weight', 'value', 'is_active', 'option_group_id', 'option_group_id.name' ],
          'options': {
            'limit': 1000,
            'sort': 'id'
          }
        })
        .done(function (result) {
          result.optionGroupData = _(result.values)
            .map(function (optionValue) {
              return [
                optionValue['option_group_id.name'],
                optionValue.option_group_id
              ];
            })
            .zipObject()
            .value();

          deferred.resolve(result);
        })
        .error(function (result) {
          deferred.reject('An error occured while fetching items');
        });

        return deferred.promise;
      },

      getNewJobRole: function (contractId) {
        // Creates new JobRole depending on contract id and returns promise
        return CRM.api3('HrJobRoles', 'create', {
          'sequential': 1,
          'job_contract_id': contractId,
          'title': ''
        });
      },

      /**
       * Returns the current departments of a given contract
       *
       * @param  {int} contractId
       * @return {Promise} resolves to an array of departments
       */
      getCurrentDepartments: function (contractId) {
        return CRM.api3('HrJobRoles', 'getcurrentdepartments', {
          'sequential': 1,
          'job_contract_id': contractId
        })
        .then(function (result) {
          return result.values;
        });
      }
    };
  }]);
});
