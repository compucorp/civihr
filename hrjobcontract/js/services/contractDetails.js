define(['services/services',
        'services/utils'], function (services) {

    services.factory('ContractDetailsService', ['$resource', 'settings', '$q', 'UtilsService', '$log',
        function ($resource, settings, $q, UtilsService, $log) {
            $log.debug('Service: ContractDetailsService');

        var ContractDetails = $resource(settings.pathRest, {
            action: 'get',
            entity: 'HRJobDetails',
            json: {}
        });

        return {
            getOne: function(params) {

                if ((!params || typeof params !== 'object') ||
                    (!params.jobcontract_id && !params.jobcontract_revision_id) ||
                    (params.jobcontract_id && typeof +params.jobcontract_id !== 'number') ||
                    (params.jobcontract_revision_id && typeof +params.jobcontract_revision_id!== 'number')) {
                    return null;
                }

                params.sequential = 1;
                params.debug = settings.debug;

                var deffered = $q.defer(),
                    val;

                ContractDetails.get({json: params}, function(data){

                    if (UtilsService.errorHandler(data,'Unable to fetch contract details',deffered)) {
                        return
                    }

                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to fetch contract details');
                });

                return deffered.promise;
            },
            getOptions: function(fieldName, callAPI){
                var deffered = $q.defer(), data;

                if (!callAPI) {
                    data = settings.CRM.options.HRJobDetails || {};

                    if (fieldName && typeof fieldName === 'string') {
                        data = data[fieldName];
                    }

                    deffered.resolve(data || {});
                } else {
                    //TODO call2API
                }

                return deffered.promise;
            },
            getFields: function(params){

                if (params && typeof params !== 'object') {
                    return null;
                }

                if (!params || typeof params !== 'object') {
                    params = {};
                }

                var deffered = $q.defer(),
                    crmFields = settings.CRM.fields;

                if (crmFields && crmFields.HRJobDetails) {
                    deffered.resolve(crmFields.HRJobDetails);
                } else {
                    params.sequential = 1;

                    ContractDetails.get({
                        action: 'getfields',
                        json: params
                    }, function(data){

                        if (!data.values) {
                            deffered.reject('Unable to fetch contract details fields');
                        }

                        deffered.resolve(data.values);
                    },function(){
                        deffered.reject('Unable to fetch contract details fields');
                    });
                }

                return deffered.promise;
            },
            save: function(contractDetails){

                if (!contractDetails || typeof contractDetails !== 'object') {
                    return null;
                }

                var deffered = $q.defer(),
                    params = angular.extend({
                        sequential: 1,
                        debug: settings.debug
                    },contractDetails),
                    val;

                ContractDetails.save({
                    action: 'create',
                    json: params
                }, null, function(data){

                    if (UtilsService.errorHandler(data,'Unable to create contract details',deffered)) {
                        return
                    }

                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to create contract details');
                });

                return deffered.promise;
            },
            model: function(fields){

                var deffered = $q.defer();

                function createModel(fields) {
                    var i = 0, len = fields.length, model = {};

                    for (i; i < len; i++) {
                        model[fields[i].name] = '';
                    }

                    if (typeof model.id !== 'undefined') {
                        model.id = null;
                    }

                    if (typeof model.jobcontract_revision_id !== 'undefined') {
                        model.jobcontract_revision_id = null;
                    }

                    if (typeof model.location !== 'undefined') {
                        model.location = null;
                    }

                    return model;
                }

                if (fields) {
                    deffered.resolve(createModel(fields));
                } else {
                    this.getFields().then(function(fields){
                        deffered.resolve(createModel(fields));
                    });
                }

                return deffered.promise;
            }
        }

    }]);

});