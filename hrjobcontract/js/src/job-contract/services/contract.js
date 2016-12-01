define([
    'job-contract/services/services'
], function (services) {
    'use strict';

    services.factory('Contract',['$resource', 'settings', '$log', function ($resource, settings, $log) {
        $log.debug('Service: Contract');

        return $resource(settings.pathRest,{
                action: 'get',
                entity: 'HRJobContract',
                json: {}
            })
    }]);

    services.factory('ContractService', [
        '$log', '$q', 'Contract', 'ContractRevisionService', 'settings', 'UtilsService', 'DOMEventTrigger',
        function ($log, $q, Contract, ContractRevisionService, settings, UtilsService, DOMEventTrigger) {
            $log.debug('Service: ContractService');

        return {
            get: function(contactId) {
                var deffered = $q.defer(), params = {};

                if (!CRM || !CRM.jobContractTabApp || !CRM.jobContractTabApp.contractList) {
                    params = {
                        sequential: 1,
                        contact_id: settings.contactId,
                        deleted: 0
                    };

                    if (contactId && typeof +contactId === 'number') {
                        params.contact_id = contactId;
                    }

                    Contract.get({json: params}, function(data){

                        if (UtilsService.errorHandler(data,'Unable to fetch contract list',deffered)) {
                            return
                        }

                        deffered.resolve(data.values);
                    },function(){
                        deffered.reject('Unable to fetch contract list');
                    });
                } else {
                    deffered.resolve(CRM.jobContractTabApp.contractList);
                }

                return deffered.promise;
            },
          /**
           * Perform an ajax request and call HrJobContract => getcurrentcontract
           * API method which is used to get the current contract for the contact
           * or null if it is not exist.
           *
           * @param contactId :the current contact ID
           */
            getCurrentContract: function(contactId) {
              var deffered = $q.defer();

              Contract.get({action: 'getcurrentcontract', json: {'contact_id': contactId} }, function(data){
                if (data.is_error)  {
                  deffered.reject('Unable to fetch the current contract');
                }
                deffered.resolve(data.values);
              },function(){
                deffered.reject('Unable to fetch the current contract');
              });

              return deffered.promise;
            },

            /**
             * Triggers the update of the contact header via the `hrui` extension
             * by emitting a DOM event with the contract data
             */
            updateHeaderInfo: function () {
              this.getCurrentContract(settings.contactId)
                .then(function (currentContract) {
                  DOMEventTrigger('updateContactHeader', { contract: currentContract });
                })
                .catch(function (error) {
                  console.log(error);
                });
            },
            getOne: function(contractId, contactId){

                if (!contractId || typeof +contractId !== 'number') {
                    return null;
                }

                var deffered = $q.defer(),
                    params = {
                        deleted: 0,
                        sequential: 1,
                        contact_id: settings.contactId,
                        id: contractId
                    },
                    val;

                if (contactId && typeof +contactId === 'number') {
                    params.contact_id = contactId;
                }

                Contract.get({json: params}, function(data){
                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to fetch contract data');
                });

                return deffered.promise;
            },
            getRevision: function(contractId) {

                if (!contractId || typeof +contractId !== 'number') {
                    return null;
                }

                var deffered = $q.defer(),
                    params = {
                        deleted: 0,
                        options: {
                            limit: 0
                        },
                        sequential: 1,
                        jobcontract_id: contractId
                    };

                ContractRevisionService.get({json: params}, function(data){
                    deffered.resolve(data.values);
                },function(){
                    deffered.reject('Unable to fetch contract revisions');
                });

                return deffered.promise;

            },
            getRevisionOptions: function(fieldName, callAPI){
                var deffered = $q.defer(), data;

                if (!callAPI) {
                    var data = settings.CRM.options.HRJobContractRevision || {};

                    if (fieldName && typeof fieldName === 'string') {
                        data = data[optionGroup];
                    }

                    deffered.resolve(data || {});
                } else {
                    //TODO call2API
                }

                return deffered.promise;
            },
            save: function(contractDetails) {

                if ((!contractDetails || typeof contractDetails !== 'object') ||
                    (!contractDetails.id || typeof +contractDetails.id !== 'number')) {
                    return null;
                }

                var deffered = $q.defer(),
                    params = angular.extend({
                        deleted: 0,
                        sequential: 1
                    },contractDetails),
                    val;

                Contract.save({
                    action: 'create',
                    json: params
                }, null, function(data){
                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to fetch contract contract data');
                });

                return deffered.promise;

            },
            saveRevision: function(revisionDetails) {

                if ((!revisionDetails || typeof revisionDetails !== 'object') ||
                    (!revisionDetails.id || typeof +revisionDetails.id !== 'number')) {
                    return null;
                }

                var deffered = $q.defer(),
                    params = angular.extend({
                        deleted: 0,
                        sequential: 1
                    },revisionDetails),
                    val;

                ContractRevisionService.save({
                    action: 'create',
                    json: params
                }, null, function(data){
                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to fetch contract revision');
                });

                return deffered.promise;

            },
            delete: function(contractId) {

                if (!contractId || typeof +contractId !== 'number') {
                    return null;
                }

                var deffered = $q.defer();

                Contract.delete({
                    action: 'deletecontract',
                    json: { id: contractId }
                }, function(data){
                    deffered.resolve(data);
                },function(){
                    deffered.reject('Could not delete contract ID:'+contractId);
                });

                return deffered.promise;
            },
            deleteRevision: function(revisionId) {

                if (!revisionId || typeof +revisionId !== 'number') {
                    return null;
                }

                var deffered = $q.defer(),
                    val;

                ContractRevisionService.save({
                    action: 'create',
                    json: {
                        sequential: 1,
                        deleted: 1,
                        id: revisionId
                    }
                }, null, function(data){
                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to delete contract revision id: ' + revisionId);
                });

                return deffered.promise;
            },

            /**
             * Returns the full details of a contract via the related custom api endpoint
             *
             * @param  {int} contractId
             * @return {Promise} resolves with the api response
             */
            fullDetails: function (contractId) {
              if (!contractId || typeof +contractId !== 'number') {
                return null;
              }

              var deferred = $q.defer();

              Contract.get({
                action: 'getfulldetails',
                json: { jobcontract_id: contractId }
              }, function (data) {
                deferred.resolve(data);
              },function () {
                deferred.reject('Could not fetch full details for contract ID:' + contractId);
              });

              return deferred.promise;
            }
        }
    }]);
});
