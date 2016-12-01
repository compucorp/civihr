define([
  'common/lodash',
  'contact-summary/modules/services',
  'contact-summary/services/api',
  'contact-summary/services/contactDetails',
  'contact-summary/services/model',
], function (_, services) {
  'use strict';

    var promiseCache = {};

  /**
   * @param {ApiService} Api
   * @param {ModelService} Model
   * @param {ContactDetailsService} ContactDetails
   * @param $q
   * @param $log
   * @returns {ModelService|Object|ItemService|*}
   * @constructor
   */
  function ContractService($q, $log, Api, Model, ContactDetails) {
    $log.debug('Service: Contract Service');

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * TODO: Implement a collection and extend it instead
     *
     * @ngdoc service
     * @name ContractService
     */
    //var factory = Model.createInstance();
    var factory = {};

    initializeCollection();

    factory.getCollection = function () {
      return this.collection.get();
    };

    /**
     * @ngdoc method
     * @name get
     * @methodOf ContractService
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(ContractService|ModelService)} */
      var self = this;

      return init().then(function () {
        //return self.getData();
        return self.getCollection();
      });
    };

    /**
     * A primary contract is:
     * 1. (If exists) a contract with is_primary=1 that is active, or
     * 2. The most recent contract that is active
     *
     * @ngdoc method
     * @name getPrimary
     * @methodOf ContractService
     */
    factory.getPrimary = function () {
      return this.get().then(function (response) {
        var sortedContracts = _.sortBy(response, function (o) {
          return [o.end_date, +o.is_primary];
        });

        return _.last(sortedContracts) || {};
      });
    };

    /**
     * Reset contracts and promiseCache to initial state
     * @ngdoc method
     * @name resetContracts
     * @methodOf ContractService
     * @returns void
     */
    factory.resetContracts = function () {
      contracts = [];
      promiseCache = {};
      initializeCollection();
    };

    factory.getContracts = function () {
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
    };

    /**
     * @ngdoc method
     * @name getContractDetails
     * @methodOf ContractService
     * @param id
     * @returns {*}
     */
    factory.getContractDetails = function (id) {
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

      if (!promiseCache.getContractDetails) {
            promiseCache.getContractDetails = Api.post('HRJobDetails', data, 'get')
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

          return promiseCache.getContractDetails;
    };

    /**
     * Get an object containing 'days', 'months' and 'years' keys with
     * integer values of total Length of Service value, for example:
     * {
     *   days: 9,
     *   months: 2,
     *   years: 0
     * }
     *
     * @name getLengthOfService
     * @methodOf ContractService
     * @returns {*}
     */
    factory.getLengthOfService = function () {
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
          )
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
    };

    /////////////////////
    // Private Members //
    /////////////////////

    var contracts = [];

    function initializeCollection() {
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

    function init() {
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

    function assembleContracts() {
      var deferred = $q.defer(), promises = [];

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

    return factory;
  }

  services.factory('ContractService', ['$q', '$log', 'ApiService', 'ModelService', 'ContactDetailsService', ContractService]);
});
