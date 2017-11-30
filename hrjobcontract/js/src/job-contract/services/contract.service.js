/* eslint-env amd */

define([
  'common/lodash',
  'job-contract/services/services'
], function (_, services) {
  'use strict';

  services.factory('Contract', ['$resource', 'settings', '$log', function ($resource, settings, $log) {
    $log.debug('Service: Contract');

    return $resource(settings.pathRest, {
      action: 'get',
      entity: 'HRJobContract',
      json: {}
    });
  }]);

  services.factory('ContractService', [
    '$log', '$q', 'Contract', 'ContractRevisionService', 'settings', 'UtilsService', 'DOMEventTrigger', 'AbsenceType',
    function ($log, $q, Contract, ContractRevisionService, settings, UtilsService, DOMEventTrigger, AbsenceType) {
      $log.debug('Service: ContractService');

      /**
       * The API returns values as strings, so we convert them to booleans to
       * make it easy to use them inside conditions
       *
       * @param {Object} contract as returned by the API
       * @param {Object} absenceTypes indexed by their IDs
       */
      function adjustAddPublicHolidaysValue (contract, absenceTypes) {
        _.each(contract.leave, function (leave) {
          leave.add_public_holidays =
            absenceTypes[leave.leave_type].calculation_unit_name !== 'hours' &&
            !!parseInt(leave.add_public_holidays);
        });
      }

      /**
       * Filters out disabled or non-existing anymore Absence Types
       * from the contract leave details
       *
       * @param  {Object} contractLeaves leave property of contract (as returned by the API)
       * @param  {Array}  absenceTypes as returned by API
       * @return {Array}  filtered out contract leave details
       */
      function filterOutDisabledAbsenceTypes (contractLeaves, absenceTypes) {
        return _.filter(contractLeaves, function (leave) {
          if (_.find(absenceTypes, { id: leave.leave_type })) {
            return leave;
          }
        });
      }

      return {
        get: function (contactId) {
          var deffered = $q.defer();
          var params = {};

          if (!CRM || !CRM.jobContractTabApp || !CRM.jobContractTabApp.contractList) {
            params = {
              sequential: 1,
              contact_id: settings.contactId,
              deleted: 0
            };

            if (contactId && typeof +contactId === 'number') {
              params.contact_id = contactId;
            }

            Contract.get({
              json: params
            }, function (data) {
              if (UtilsService.errorHandler(data, 'Unable to fetch contract list', deffered)) {
                return;
              }

              deffered.resolve(data.values);
            }, function () {
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
        getCurrentContract: function (contactId) {
          var deffered = $q.defer();

          Contract.get({
            action: 'getcurrentcontract',
            json: {
              'contact_id': contactId
            }
          }, function (data) {
            if (data.is_error) {
              deffered.reject('Unable to fetch the current contract');
            }
            deffered.resolve(data.values);
          }, function () {
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
              DOMEventTrigger('updateContactHeader', {
                contract: currentContract
              });
            })
            .catch(function (error) {
              console.log(error);
            });
        },
        getOne: function (contractId, contactId) {
          var val;
          var deffered = $q.defer();
          var params = {
            deleted: 0,
            sequential: 1,
            contact_id: settings.contactId,
            id: contractId
          };

          if (!contractId || typeof +contractId !== 'number') {
            return null;
          }

          if (contactId && typeof +contactId === 'number') {
            params.contact_id = contactId;
          }

          Contract.get({
            json: params
          }, function (data) {
            val = data.values;
            deffered.resolve(val.length === 1 ? val[0] : null);
          }, function () {
            deffered.reject('Unable to fetch contract data');
          });

          return deffered.promise;
        },
        getRevision: function (contractId) {
          var deffered = $q.defer();
          var params = {
            deleted: 0,
            options: {
              limit: 0
            },
            sequential: 1,
            jobcontract_id: contractId
          };

          if (!contractId || typeof +contractId !== 'number') {
            return null;
          }

          ContractRevisionService.get({
            json: params
          }, function (data) {
            deffered.resolve(data.values);
          }, function () {
            deffered.reject('Unable to fetch contract revisions');
          });

          return deffered.promise;
        },
        getRevisionOptions: function (fieldName, callAPI) {
          var data;
          var deffered = $q.defer();

          if (!callAPI) {
            data = settings.CRM.options.HRJobContractRevision || {};

            if (fieldName && typeof fieldName === 'string') {
              data = data[fieldName];
            }

            deffered.resolve(data || {});
          } else {
            // TODO call2API
          }

          return deffered.promise;
        },
        save: function (contractDetails) {
          var val;
          var deffered = $q.defer();
          var params = _.extend({
            deleted: 0,
            sequential: 1
          }, contractDetails);

          if ((!contractDetails || typeof contractDetails !== 'object') ||
            (!contractDetails.id || typeof +contractDetails.id !== 'number')) {
            return null;
          }

          Contract.save({
            action: 'create',
            json: params
          }, null, function (data) {
            val = data.values;
            deffered.resolve(val.length === 1 ? val[0] : null);
          }, function () {
            deffered.reject('Unable to fetch contract contract data');
          });

          return deffered.promise;
        },
        saveRevision: function (revisionDetails) {
          var val;
          var deffered = $q.defer();
          var params = _.extend({
            deleted: 0,
            sequential: 1
          }, revisionDetails);

          if ((!revisionDetails || typeof revisionDetails !== 'object') ||
            (!revisionDetails.id || typeof +revisionDetails.id !== 'number')) {
            return null;
          }

          ContractRevisionService.save({
            action: 'create',
            json: params
          }, null, function (data) {
            val = data.values;
            deffered.resolve(val.length === 1 ? val[0] : null);
          }, function () {
            deffered.reject('Unable to fetch contract revision');
          });

          return deffered.promise;
        },
        delete: function (contractId) {
          var deffered = $q.defer();

          if (!contractId || typeof +contractId !== 'number') {
            return null;
          }

          Contract.delete({
            action: 'deletecontract',
            json: {
              id: contractId
            }
          }, function (data) {
            deffered.resolve(data);
          }, function () {
            deffered.reject('Could not delete contract ID:' + contractId);
          });

          return deffered.promise;
        },
        deleteRevision: function (revisionId) {
          var val;
          var deffered = $q.defer();

          if (!revisionId || typeof +revisionId !== 'number') {
            return null;
          }

          ContractRevisionService.save({
            action: 'create',
            json: {
              sequential: 1,
              deleted: 1,
              id: revisionId
            }
          }, null, function (data) {
            val = data.values;
            deffered.resolve(val.length === 1 ? val[0] : null);
          }, function () {
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
          var deferred = $q.defer();

          if (!contractId || typeof +contractId !== 'number') {
            return null;
          }

          AbsenceType.all()
          .then(AbsenceType.loadCalculationUnits)
          .then(function (absenceTypes) {
            return Contract.get({
              action: 'getfulldetails',
              json: {
                jobcontract_id: contractId
              }
            }, function (contract) {
              contract.leave = filterOutDisabledAbsenceTypes(contract.leave, absenceTypes);

              adjustAddPublicHolidaysValue(contract, _.indexBy(absenceTypes, 'id'));
              deferred.resolve(contract);
            }, function () {
              deferred.reject('Could not fetch full details for contract ID:' + contractId);
            });
          });

          return deferred.promise;
        }
      };
    }
  ]);
});
