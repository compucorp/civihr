define([
  'common/lodash',
  'job-roles/services/services'
], function (_, services) {
    'use strict';

    services.factory('HRJobRolesService', ['$log', '$q', '$filter', function ($log, $q, $filter) {

        /**
         * Extracts the contract revisions details from the chained api calls
         * properties, then removes the current one and format the dates
         *
         * @param  {Object} contract
         */
        function processContractRevisions(contract) {
          var contractRevisions = contract['api.HRJobContractRevision.get'].values;
          delete(contract['api.HRJobContractRevision.get']);

          contract.revisions = _.compact(contractRevisions
            .map(function (revision) {
              var details = revision['api.HRJobDetails.getsingle'];

              if (details.period_start_date === contract.period_start_date
              && details.period_end_date === contract.period_end_date) {
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

            getAllJobRoles: function (job_contract_ids) {

                var deferred = $q.defer();

                CRM.api3('HrJobRoles', 'get', {
                    "sequential": 1,
                    "return": "id,job_contract_id,title,description,status,funder,funder_val_type,percent_pay_funder,amount_pay_funder,cost_center,cost_center_val_type,percent_pay_cost_center,amount_pay_cost_center,level_type,location,region,department,end_date,start_date",
                    "job_contract_id": { "IN": job_contract_ids }
                }).done(function (result) {

                    // Passing data to deferred's resolve function on successful completion
                    deferred.resolve(result);

                }).error(function (result) {

                    // Sending a friendly error message in case of failure
                    deferred.reject("An error occured while fetching items");

                });

                // Returning the promise object
                return deferred.promise;

            },

            deleteJobRole: function (job_role_id) {

                var deferred = $q.defer();

                CRM.api3('HrJobRoles', 'delete', {
                    "sequential": 1,
                    "id": job_role_id
                }).done(function (result) {

                    // Passing data to deferred's resolve function on successful completion
                    deferred.resolve(result);

                }).error(function (result) {

                    // Sending a friendly error message in case of failure
                    deferred.reject("An error occured while deleting items");

                });

                // Returning the promise object
                return deferred.promise;

            },

            createJobRole: function (job_roles_data) {

                // Define funder IDs string
                var funders = "|";

                // Set the funder types
                var funder_types = "|";

                // Set the percent value for the funder
                var percent_funders = "|";

                // Set the amount value for the funder
                var amount_funders = "|";

                // Define cost_center IDs string
                var cost_centers = "|";

                // Set the cost_center types
                var cost_center_types = "|";

                // Set the percent value for the cost_center
                var percent_cost_centers = "|";

                // Set the amount value for the cost_center
                var amount_cost_centers = "|";

                // If we have any funders added, loop and save them
                if (typeof job_roles_data.funders !== "undefined") {

                    // Loop funders and set up the data to store the funders
                    for (var i = 0, l = job_roles_data.funders.length; i < l; i++) {

                        if (job_roles_data.funders[i]) {
                            funders += job_roles_data.funders[i]['funder_id']['id'] + "|";
                            funder_types += job_roles_data.funders[i]['type'] + "|";
                            percent_funders += job_roles_data.funders[i]['percentage'] + "|";
                            amount_funders += job_roles_data.funders[i]['amount'] + "|";
                        }
                    }
                }

                // If we have any cost_centers added, loop and save them
                if (typeof job_roles_data.cost_centers !== "undefined") {

                    // Loop cost_centers and set up the data to store the cost_centers
                    for (i = 0, l = job_roles_data.cost_centers.length; i < l; i++) {

                        if (job_roles_data.cost_centers[i]) {
                            cost_centers += job_roles_data.cost_centers[i]['cost_centre_id'] + "|";
                            cost_center_types += job_roles_data.cost_centers[i]['type'] + "|";
                            percent_cost_centers += job_roles_data.cost_centers[i]['percentage'] + "|";
                            amount_cost_centers += job_roles_data.cost_centers[i]['amount'] + "|";
                        }
                    }
                }

                var deferred = $q.defer();
                //FIXME 'solution' to the bug failing saving correct dates to DB a first save
                this.getNewJobRole(job_roles_data.job_contract_id).then(function (result) {

                    return CRM.api3('HrJobRoles', 'update', {
                        "id": result.id,
                        "sequential": 1,
                        "job_contract_id": job_roles_data.job_contract_id,
                        "title": job_roles_data.title,
                        "description": job_roles_data.description,
                        "funder": funders,
                        "funder_val_type": funder_types,
                        "percent_pay_funder": percent_funders,
                        "amount_pay_funder": amount_funders,
                        "cost_center": cost_centers,
                        "cost_center_val_type": cost_center_types,
                        "percent_pay_cost_center": percent_cost_centers,
                        "amount_pay_cost_center": amount_cost_centers,
                        "level_type": job_roles_data.level,
                        "location": job_roles_data.location,
                        "region": job_roles_data.region,
                        "department": job_roles_data.department,
                        "start_date": job_roles_data.newStartDate,
                        "end_date": job_roles_data.newEndDate || null
                    });
                }).then(function (response) {
                    // Passing data to deferred's resolve function on successful completion
                    deferred.resolve(response);
                }, function (result) {
                    // Sending a friendly error message in case of failure
                    deferred.reject("An error occured while adding items");
                });

                // Returning the promise object
                return deferred.promise;

            },

            updateJobRole: function (role_id, job_roles_data) {

                // Define funder IDs string
                var funders = "|";

                // Set the funder types
                var funder_types = "|";

                // Set the percent value for the funder
                var percent_funders = "|";

                // Set the amount value for the funder
                var amount_funders = "|";

                // Define cost_center IDs string
                var cost_centers = "|";

                // Set the cost_center types
                var cost_center_types = "|";

                // Set the percent value for the cost_center
                var percent_cost_centers = "|";

                // Set the amount value for the cost_center
                var amount_cost_centers = "|";

                // If we have any funders added, loop and save them
                if (typeof job_roles_data.funders !== "undefined") {
                    // Loop funders and set up the data to store the funders
                    for (var i = 0, l = job_roles_data.funders.length; i < l; i++) {

                        if (job_roles_data.funders[i]) {
                            funders += job_roles_data.funders[i]['funder_id']['id'] + "|";
                            funder_types += job_roles_data.funders[i]['type'] + "|";
                            percent_funders += job_roles_data.funders[i]['percentage'] + "|";
                            amount_funders += job_roles_data.funders[i]['amount'] + "|";
                        }
                    }
                }

                // If we have any cost_centers added, loop and save them
                if (typeof job_roles_data.cost_centers !== "undefined") {

                    // Loop cost_centers and set up the data to store the cost_centers
                    for (var i = 0, l = job_roles_data.cost_centers.length; i < l; i++) {

                        if (job_roles_data.cost_centers[i]) {
                            cost_centers += job_roles_data.cost_centers[i]['cost_centre_id'] + "|";
                            cost_center_types += job_roles_data.cost_centers[i]['type'] + "|";
                            percent_cost_centers += job_roles_data.cost_centers[i]['percentage'] + "|";
                            amount_cost_centers += job_roles_data.cost_centers[i]['amount'] + "|";
                        }
                    }
                }

                var deferred = $q.defer();

                CRM.api3('HrJobRoles', 'create', {
                    "sequential": 1,
                    "id": role_id,
                    "job_contract_id": job_roles_data.job_contract_id,
                    "title": job_roles_data.title,
                    "description": job_roles_data.description,
                    "status": job_roles_data.status,
                    "funder": funders,
                    "funder_val_type": funder_types,
                    "percent_pay_funder": percent_funders,
                    "amount_pay_funder": amount_funders,
                    "cost_center": cost_centers,
                    "cost_center_val_type": cost_center_types,
                    "percent_pay_cost_center": percent_cost_centers,
                    "amount_pay_cost_center": amount_cost_centers,
                    "level_type": job_roles_data.level,
                    "location": job_roles_data.location,
                    "region": job_roles_data.region,
                    "start_date": job_roles_data.start_date,
                    "end_date": job_roles_data.end_date || 0,
                    "department": job_roles_data.department

                }).done(function (result) {

                    // Passing data to deferred's resolve function on successful completion
                    deferred.resolve(result);

                }).error(function (result) {

                    // Sending a friendly error message in case of failure
                    deferred.reject("An error occured while updating items");

                });

                // Returning the promise object
                return deferred.promise;

            },

            getContactList: function (sortName, idsList) {
              var deferred = $q.defer();

              CRM.api3('Contact', 'get', {
                "sequential": 1,
                "return": "id, sort_name",
                "id": _.isArray(idsList) ? { 'IN': idsList } : null,
                "sort_name": sortName || null
              }).done(function (result) {
                deferred.resolve(result);
              }).error(function (result) {
                deferred.reject("An error occured while fetching items");
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
                deferred.reject("An error occured while fetching items");
              });

              return deferred.promise;
            },

            getNewJobRole: function (contract_id) {
                //Creates new JobRole depending on contract id and returns promise
                return CRM.api3('HrJobRoles', 'create', {
                    "sequential": 1,
                    "job_contract_id": contract_id,
                    "title": ''
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
        }
    }]);

});
