define(['services/services'], function (services) {

    services.factory('API', ['$resource','$q','settings','$log', function ($resource, $q, settings, $log) {
        $log.debug('Service: UtilsService');

        return {
            resource: function(entity, action, json) {

                if ((!entity || typeof entity !== 'string') ||
                    (!action || typeof action !== 'string') ||
                    (json && typeof json !== 'object')) {
                    return null;
                }

                return $resource(settings.pathRest,{
                    action: action,
                    entity: entity,
                    json: json
                })
            },
            getOne: function(entity, params) {

                if ((!entity || typeof entity !== 'string') ||
                    (params && typeof params !== 'object')) {
                    return null;
                }

                var deffered = $q.defer(),
                    json = angular.extend({
                        sequential: 1
                    },params),
                    val;

                this.resource(entity,'get', json).get(function(data){
                    val = data.values;
                    deffered.resolve(val.length == 1 ? val[0] : null);
                },function(){
                    deffered.reject('Unable to fetch data');
                });

                return deffered.promise;
            },
            get: function(entity, params) {

                if ((!entity || typeof entity !== 'string') ||
                    (params && typeof params !== 'object')) {
                    return null;
                }

                var deffered = $q.defer(),
                    json = angular.extend({
                        sequential: 1
                    },params);

                this.resource(entity,'get', json).get(function(data){
                    deffered.resolve(data.values);
                },function(){
                    deffered.reject('Unable to fetch data');
                });

                return deffered.promise;
            }
        }
    }]);

    services.factory('testAPI', ['$resource','settings', function ($resource, settings) {
        return {
            resource: function(entity, action, json) {

                if ((!entity || typeof entity !== 'string') ||
                    (!action || typeof action !== 'string') ||
                    (json && typeof json !== 'object')) {
                    return null;
                }

                return $resource(settings.pathApp+'js/data/'+entity+'.json',{
                    action: action,
                    entity: entity,
                    json: json
                })
            }
        }
    }]);

    services.factory('UtilsService', ['API','testAPI','settings','$q','$log', '$timeout', function (API, testAPI, settings, $q, $log, $timeout) {
        return {
            getAbsenceType: function(){
                var deffered = $q.defer();

                API.resource('HRAbsenceType','get', {
                    "return": "id,name,title"
                }).get(function(data){
                    deffered.resolve(data.values);
                },function(){
                    deffered.reject('Unable to fetch absence types');
                });

                return deffered.promise;
            },
            getHoursLocation: function(){
                var deffered = $q.defer();

                API.resource('HRHoursLocation','get',{
                    sequential: 1
                }).get(function(data){
                    deffered.resolve(data.values);
                },function(){
                    deffered.reject('Unable to fetch standard hours');
                });

                return deffered.promise;
            },
            getPayScaleGrade: function(){
                var deffered = $q.defer();

                API.resource('HRPayScale','get',{
                    sequential: 1
                }).get(function(data){
                    deffered.resolve(data.values);
                },function(){
                    deffered.reject('Unable to fetch standard hours');
                });

                return deffered.promise;
            },
            prepareEntityIds: function(entityObj, contractId, revisionId){

                function setIds(entityObj){
                    entityObj.jobcontract_id = contractId;
                    delete entityObj.id;
                    revisionId ? entityObj.jobcontract_revision_id = revisionId : delete entityObj.jobcontract_revision_id;
                }

                if (angular.isArray(entityObj)) {
                    var i = 0, len = entityObj.length;
                    for (i; i < len; i++) {
                        setIds(entityObj[i]);
                    }
                    return
                }

                if (angular.isObject(entityObj)) {
                    setIds(entityObj);
                    return
                }

            },
            errorHandler: function(data, msg, deffered){
                if (data.is_error) {
                    $log.error(data.error_code + '\n'+data.error_message);

                    if (deffered) {
                        deffered.reject(data.error_code + '\n'+data.error_message);
                    }

                    if (data.trace) {
                        $log.error(data.trace);
                    }
                    return true;
                }

                if (!data.values) {
                    $log.error(msg || 'Unknown Error');

                    if (deffered) {
                        deffered.reject(msg || 'Unknown Error');
                    }
                    return true;
                }
            }
        }
    }]);

});