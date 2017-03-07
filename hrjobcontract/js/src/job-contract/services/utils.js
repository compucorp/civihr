define([
    'job-contract/services/services'
], function (services) {
    'use strict';

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

    services.factory('UtilsService', [
      'API', 'testAPI', 'settings', '$q', '$log', '$window',
      function (API, testAPI, settings, $q, $log, $window) {
        return {

            /**
             * Returns a promise that resolves to an array with including all of the Absence Types.
             *
             * Each returned Absence Type includes these fields:
             * - id
             * - title
             * - default_entitlement
             * - add_public_holiday_to_entitlement
             *
             * @returns {Promise}
             */
            getAbsenceTypes: function(){
                var deffered = $q.defer();

                API.resource('AbsenceType','get', {
                  "return": "id,title,default_entitlement,add_public_holiday_to_entitlement"
                }).get(function (data) {
                  angular.forEach(data.values, function (value) {
                    value.add_public_holiday_to_entitlement = !!parseInt(value.add_public_holiday_to_entitlement);
                  });

                  deffered.resolve(data.values);
                },function () {
                  deffered.reject('Unable to fetch absence types');
                });

                return deffered.promise;
            },

            getHoursLocation: function(){
                var deffered = $q.defer();

                API.resource('HRHoursLocation','get',{
                    sequential: 1,
                    is_active: 1
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
                    sequential: 1,
                    is_active: 1
                }).get(function(data){
                    deffered.resolve(data.values);
                },function(){
                    deffered.reject('Unable to fetch standard hours');
                });

                return deffered.promise;
            },

            /**
             * Returns a promise that resolves the an int with the number of Public Holidays in the
             * current Absence Period
             *
             * @returns {Promise}
             */
            getNumberOfPublicHolidaysInCurrentPeriod: function() {
              var deffered = $q.defer();

              API.resource('PublicHoliday', 'getcountforcurrentperiod', {
                sequential: 1
              }).get(function (data) {
                var number = parseInt(data.result) || 0;

                deffered.resolve(number);
              }, function () {
                deffered.reject('Unable to fetch the number of public holidays in current period');
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
            },

            /**
             * Returns the URL to the Manage Entitlement page.
             *
             * The given contact ID is added to the URL, as the cid parameter.
             *
             * @param {int} contactId
             */
            getManageEntitlementsPageURL: function(contactId) {
              var path = 'civicrm/admin/leaveandabsences/periods/manage_entitlements';
              var returnPath = 'civicrm/contact/view';
              var returnUrl = CRM.url(returnPath, { cid: contactId, selectedChild : 'hrjobcontract' });
              return CRM.url(path, { cid: contactId, returnUrl : returnUrl });
            }
        }
    }]);

});
