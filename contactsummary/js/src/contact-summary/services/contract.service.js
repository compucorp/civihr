/* eslint-env amd */

define([
  'common/angular',
  'common/lodash'
], function (angular, _) {
  'use strict';

  var promiseCache = {};

  contractService.__name = 'contractService';
  contractService.$inject = ['$q', '$log', 'settings', 'apiService', 'modelService', 'contactDetailsService'];

  function contractService ($q, $log, settings, Api, Model, ContactDetails) {
    $log.debug('Service: Contract Service');

    var contracts = [];
    var factory = {};

    factory.get = get;
    factory.getCollection = getCollection;
    factory.getContracts = getContracts;
    factory.getContractDetails = getContractDetails;
    factory.getLengthOfService = getLengthOfService;
    factory.getOptions = getOptions;
    factory.getPrimary = getPrimary;
    factory.resetContracts = resetContracts;

    initializeCollection();

    return factory;

    function assembleContracts () {
      var deferred = $q.defer();
      var promises = [];

      angular.forEach(contracts, function (contract) {
        var assembledContract = {};

        assembledContract.id = contract.id;
        assembledContract.is_primary = contract.is_primary;
        assembledContract.is_current = contract.is_current;
        assembledContract.revision_id = null;

        if (contract.api_HRJobContractRevision_getcurrentrevision) {
          assembledContract.revision_id = contract.api_HRJobContractRevision_getcurrentrevision.values.id;
        }

        var promise = factory.getContractDetails(contract.id)
          .then(function (response) {
            assembledContract.title = response.title;
            assembledContract.start_date = response.period_start_date;
            assembledContract.end_date = response.period_end_date;
            assembledContract.type = response.contract_type;
            assembledContract.pay = response.pay;
            assembledContract.hours = response.hours;
          })
          .then(function () {
            factory.collection.insertItem(contract.id, assembledContract);
          });

        promises.push(promise);
      });

      $q.all(promises)
        .catch(function (response) {
          $log.error('Something went wrong', response);
        })
        .finally(function () {
          deferred.resolve();
        });

      return deferred.promise;
    }

    /**
     * @returns {*}
     */
    function get () {
      /** @type {(contractService|ModelService)} */
      return init().then(function () {
        return factory.getCollection();
      });
    }

    function getCollection () {
      return factory.collection.get();
    }

    function getContracts () {
      var deferred = $q.defer();
      if (_.isEmpty(contracts)) {
        ContactDetails.get()
          .then(function (response) {
            var data = {
              contact_id: response.id,
              'api.HRJobContractRevision.getcurrentrevision': {jobcontract_id: '$value.id'}
            };

            return Api.get('HRJobContract', data);
          })
          .then(function (response) {
            var activeContracts = response.values.filter(function (contract) {
              return parseInt(contract.deleted) === 0;
            });

            if (activeContracts.length === 0) {
              return deferred.reject('No job contract found');
            }

            contracts = activeContracts;

            deferred.resolve(contracts);
          })
          .catch(function (response) {
            deferred.reject(response);
          });
      } else {
        deferred.resolve(contracts);
      }

      return deferred.promise;
    }

    /**
     * @param id
     * @returns {*}
     */
    function getContractDetails (id) {
      var addPay = function (details) {
        var pay = {};

        if (details.api_HRJobPay_get.values.length !== 0) {
          pay.amount = details.api_HRJobPay_get.values[0].pay_amount;
          pay.currency = details.api_HRJobPay_get.values[0].pay_currency;
        }

        details.pay = pay;
      };

      var addHours = function (details) {
        var hours = {};

        if (details.api_HRJobHour_get.values.length !== 0) {
          hours.amount = details.api_HRJobHour_get.values[0].hours_amount;
          hours.unit = details.api_HRJobHour_get.values[0].hours_unit;
        }

        details.hours = hours;
      };

      var data = {
        jobcontract_id: id,
        'api.HRJobPay.get': {'jobcontract_id': id},
        'api.HRJobHour.get': {'jobcontract_id': id}
      };

      var cacheKey = 'getContractDetails_' + id;
      if (!promiseCache[cacheKey]) {
        promiseCache[cacheKey] = Api.post('HRJobDetails', data, 'get')
          .then(function (response) {
            if (response.values.length === 0) {
              return $q.reject('No details found for contract revision with ID ' + id);
            }

            var details = response.values[0];

            addPay(details);
            addHours(details);

            return details;
          });
      }

      return promiseCache[cacheKey];
    };

    /**
     * Get an object containing 'days', 'months' and 'years' keys with
     * integer values of total Length of Service value, for example:
     * {
     *   days: 9,
     *   months: 2,
     *   years: 0
     * }
     */
    function getLengthOfService () {
      var deferred = $q.defer();
      ContactDetails.get()
        .then(function (response) {
          return Api.post(
            'HRJobContract',
            {
              sequential: 0,
              contact_id: response.id
            },
            'getlengthofserviceymd'
          );
        })
        .then(function (response) {
          if (!response.is_error) {
            deferred.resolve(response.values);
          } else {
            deferred.reject(response);
          }
        })
        .catch(function (response) {
          deferred.reject(response);
        });

      return deferred.promise;
    }

    /**
     * Returns the contract field options
     *
     * @param  {string} fieldName
     * @return {Object}
     */
    function getOptions (fieldName) {
      var options = settings.CRM.options.HRJobDetails || {};

      if (typeof fieldName === 'string') {
        options = options[fieldName];
      }

      return { 'details': options };
    }

    /**
     * A primary contract is:
     * 1. (If exists) a contract with is_primary=1 that is active, or
     * 2. The most recent contract that is active
     *
     * @return {Object}
     */
    function getPrimary () {
      return factory.get().then(function (response) {
        var sortedContracts = _.sortBy(response, function (o) {
          return [o.end_date, +o.is_primary];
        });

        return _.last(sortedContracts) || {};
      });
    }

    function init () {
      var deferred = $q.defer();
      if (_.isEmpty(factory.collection.get())) {
        factory.getContracts()
          .then(assembleContracts)
          .finally(function () {
            deferred.resolve();
          });
      } else {
        deferred.resolve();
      }

      return deferred.promise;
    }

    function initializeCollection () {
      factory.collection = {
        items: {},
        insertItem: function (key, item) {
          this.items[key] = item;
        },
        getItem: function (key) {
          return this.items[key];
        },
        set: function (collection) {
          this.items = collection;
        },
        get: function () {
          return this.items;
        }
      };
    }

    /**
     * Reset contracts and promiseCache to initial state
     */
    function resetContracts () {
      contracts = [];
      promiseCache = {};
      initializeCollection();
    }
  }

  return contractService;
});
