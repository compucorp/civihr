define(['services/services'], function (services) {

    services.factory('Contract',['$resource', 'settings', '$log', function($resource, settings, $log){
        $log.debug('Service: Contract');

        return $resource(settings.pathRest,{
                action: 'get',
                entity: 'HRJobContract',
                json: {}
            })
    }]);

    services.factory('ContractRevision',['$resource', 'settings', '$log', function($resource, settings, $log){
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