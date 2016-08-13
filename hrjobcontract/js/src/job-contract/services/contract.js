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

    services.factory('ContractRevision',['$resource', 'settings', '$log', function ($resource, settings, $log) {
        $log.debug('Service: ContractRevision');

        return $resource(settings.pathRest,{
            action: 'get',
            entity: 'HRJobContractRevision',
            json: {}
        })
    }]);

    services.factory('ContractService', ['Contract','ContractRevision','settings','$q','UtilsService','$log',
        function (Contract, ContractRevision, settings, $q, UtilsService, $log) {
            $log.debug('Service: ContractRevision');

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
             * @param  int contactId the current contact ID
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
             * called adding/editing/deleting a contract and check if there
             * is an active contract for that contact
             * if yes then the header color will be changed to blue and contract
             * info in the header will be updated .
             * if no , then the header color will be changed to red and contract info
             * in the header will be updated.
             *
             * Though it is not an optimal solution since I use JQuery here
             * inside angular app but this is the only available way currently
             * since the header is not in the scope of the angular app.
             *
             */
            changeHeaderInfo: function() {
              this.getCurrentContract(settings.contactId).then(function(currentContract){
                if(currentContract)  {
                  cj('.crm-summary-contactname-block').removeClass('crm-summary-contactname-block-without-contract');
                  cj('.crm-contact-detail-position').html('<strong>Position:</strong> '+ currentContract.position);
                  cj('.crm-contact-detail-location').html('<strong>Normal place of work:</strong> '+ currentContract.location);
                } else {
                  cj('.crm-summary-contactname-block').addClass('crm-summary-contactname-block-without-contract');
                  cj('.crm-contact-detail-position').html('');
                  cj('.crm-contact-detail-location').html('');
                }
              }).catch(function(error){
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

                ContractRevision.get({json: params}, function(data){
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

                ContractRevision.save({
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

                ContractRevision.save({
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

            }
        }

    }]);
});
