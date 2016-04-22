define([
    'job-roles/services/services'
], function (services) {
    'use strict';

    services.factory('HRJobRolesService', ['$log', '$q', '$filter', function ($log, $q, $filter) {

        return {
            /**
             * Gets all contracts and revisions
             * @param contact_id
             * @returns {promise}
             */
            getContracts: function (contact_id) {
                var deferred = $q.defer();

                /**
                 * Get contracts for given contact.
                 */
                CRM.api3('HRJobContract', 'get', {
                    "sequential": 1,
                    "contact_id": contact_id,
                    "deleted": 0,
                    "return": "title,period_end_date,period_start_date"
                }).done(function (contracts) {
                    // get revisions for each contract
                    var revisions = contracts.values.map(function (contract) {
                        return CRM.api3('HRJobContractRevision', 'get', {
                            "sequential": 1,
                            "jobcontract_id": contract.id
                        }).then(function (response) {
                            return response.values.map(function (item) {
                                return {
                                    id: item.id,
                                    contract_id: item.jobcontract_id
                                }
                            });
                        });
                    });

                    $q.all(revisions).then(function (response) {
                        // Flatten the array of revisions
                        return [].concat.apply([], response);
                    }).then(function (response) {
                        // get details for each revision
                        return $q.all(response.map(function (item) {
                            return CRM.api3('HRJobDetails', 'get', {
                                "sequential": 1,
                                "jobcontract_revision_id": item.id
                            }).then(function (result) {
                                result.job_contract_id = item.contract_id;
                                return result;
                            });
                        }));
                    }).then(function (revisions) {
                        // for each contract
                        contracts.values.forEach(function (contract) {

                            // filter revisions by contract.id and remove current
                            contract.revisions = revisions.filter(function (revision) {
                                var isCurrent = (revision.values[0].period_start_date === contract.period_start_date
                                && revision.values[0].period_end_date === contract.period_end_date);

                                return !isCurrent && revision.job_contract_id === contract.id;
                            });

                            // save revisions in contract.revisions with properly formatted dates
                            contract.revisions = contract.revisions.map(function (revisions) {
                                var revision = revisions.values[0];
                                revision.period_start_date = $filter('formatDate')(revision.period_start_date);
                                revision.period_end_date = $filter('formatDate')(revision.period_end_date);
                                return revision;
                            });
                        });

                        // Passing data to deferred's resolve function on successful completion
                        deferred.resolve(contracts);
                    });
                }).error(function (result) {

                    // Sending a friendly error message in case of failure
                    deferred.reject("An error occured while fetching items");

                });

                // Returning the promise object
                return deferred.promise;
            },

            getContractDetails: function getContractDetails(id) {
                return CRM.api3('HRJobContractRevision', 'getcurrentrevision', {
                    "sequential": 1,
                    "jobcontract_id": id
                }).then(function (result) {
                    return CRM.api3('HRJobDetails', 'get', {
                        "sequential": 1,
                        "jobcontract_id": id,
                        "jobcontract_revision_id": result.values.details_revision_id
                    });
                });
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

            getContactList: function (search_value) {

                var deferred = $q.defer();

                CRM.api3('Contact', 'get', {
                    "sequential": 1,
                    "return": "id,sort_name"
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

            getOptionValues: function (option_group_name) {

                var deferred = $q.defer();

                // Define option group names and IDs
                var optionGroupData = {};

                CRM.api3('OptionGroup', 'get', {
                    "sequential": 1,
                    "name": { "IN": option_group_name },
                    "options": { "limit": 1000 }
                }).done(function (option_group_data) {
                    if (option_group_data.is_error !== 1) {

                        var option_group_ids = [];

                        angular.forEach(option_group_data['values'], function (option_group, key) {

                            // Store the option group names and IDs
                            optionGroupData[option_group['name']] = option_group['id'];

                            // Prepare option group IDs for the API call
                            option_group_ids.push(option_group['id']);

                        });

                        CRM.api3('OptionValue', 'get', {
                            "sequential": 1,
                            "option_group_id": { "IN": option_group_ids },
                            "options": { "limit": 1000 }
                        }).done(function (result) {

                            // Pass the additional info about optionGroupData
                            result['optionGroupData'] = optionGroupData;

                            // Passing data to deferred's resolve function on successful completion
                            deferred.resolve(result);

                        }).error(function (result) {

                            // Sending a friendly error message in case of failure
                            deferred.reject("An error occured while fetching items");

                        });

                    }

                });

                // Returning the promise object
                return deferred.promise;

            },

            getNewJobRole: function getNewJobRole(contract_id) {
                //Creates new JobRole depending on contract id and returns promise
                return CRM.api3('HrJobRoles', 'create', {
                    "sequential": 1,
                    "job_contract_id": contract_id,
                    "title": ''
                });
            }

        }
    }]);

});
