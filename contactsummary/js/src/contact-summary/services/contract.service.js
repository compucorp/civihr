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
    factory.removeContract = removeContract;
    factory.getContractDetails = getContractDetails;
    factory.getLengthOfService = getLengthOfService;
    factory.getOptions = getOptions;
    factory.getPrimary = getPrimary;
    factory.resetContracts = resetContracts;

    initializeCollection();

    return factory;

    /**
     * Returns job contracts
     *
     * @return {Promise}
     */
    function getJobContracts () {
      return ContactDetails.get().then(function (contact) {
        var data = {
          contact_id: contact.id,
          'api.HRJobContractRevision.getcurrentrevision': { jobcontract_id: '$value.id' }
        };

        return Api.get('HRJobContract', data);
      });
    }

    /**
     * Filter active contracts
     *
     * @param  {Array} contracts
     * @return {Array}
     */
    function filterActiveContracts (contracts) {
      return contracts.values.filter(function (contract) {
        return parseInt(contract.deleted) === 0;
      });
    }

    /**
     * Assemble contracts
     *
     * @return {Promise}
     */
    function assembleContracts () {
      var promises = [];

      angular.forEach(contracts, function (contract) {
        var promise = factory.getContractDetails(contract.id)
          .then(function (response) {
            var currentRevision = contract.api_HRJobContractRevision_getcurrentrevision;

            return {
              'id': contract.id,
              'is_primary': contract.is_primary,
              'is_current': contract.is_current,
              'revision_id': currentRevision ? currentRevision.values.id : null,
              'title': response.title,
              'start_date': response.period_start_date,
              'end_date': response.period_end_date,
              'type': response.contract_type,
              'pay': response.pay,
              'hours': response.hours
            };
          })
          .then(function (assembledContract) {
            factory.collection.insertItem(contract.id, assembledContract);
          });

        promises.push(promise);
      });

      return $q.all(promises)
        .catch(function (response) {
          $log.error('Something went wrong', response);
        });
    }

    /**
     * Returns collection of contracts
     * @returns {*}
     */
    function get () {
      return init()
        .then(function () {
          return factory.getCollection();
        });
    }

    function getCollection () {
      return factory.collection.get();
    }

    /**
     * Returns active job contracts
     *
     * @return {Promise}
     */
    function getContracts () {
      if (!_.isEmpty(contracts)) {
        return $q.resolve(contracts);
      }

      return getJobContracts()
        .then(function (jobContracts) {
          contracts = filterActiveContracts(jobContracts);

          return contracts;
        });
    }

    /**
     * Prepares the contract details with pay and hours
     *
     * @param contractId
     * @returns {Promise}
     */
    function getContractDetails (contractId) {
      var addPay = function (details) {
        var jobPays = details.api_HRJobPay_get.values;
        var pay = {};

        if (jobPays.length !== 0) {
          pay.amount = jobPays[0].pay_amount;
          pay.currency = jobPays[0].pay_currency;
        }

        details.pay = pay;
      };
      var addHours = function (details) {
        var jobHours = details.api_HRJobHour_get.values;
        var hours = {};

        if (jobHours.length !== 0) {
          hours.amount = jobHours[0].hours_amount;
          hours.unit = jobHours[0].hours_unit;
        }

        details.hours = hours;
      };

      var cacheKey = 'getContractDetails_' + contractId;
      var data = {
        'jobcontract_id': contractId,
        'api.HRJobPay.get': {'jobcontract_id': contractId},
        'api.HRJobHour.get': {'jobcontract_id': contractId}
      };

      if (!promiseCache[cacheKey]) {
        promiseCache[cacheKey] = Api.post('HRJobDetails', data, 'get')
          .then(function (response) {
            if (response.values.length === 0) {
              return $q.reject('No details found for contract revision with ID ' + contractId);
            }

            var details = response.values[0];

            addPay(details);
            addHours(details);

            return details;
          });
      }

      return promiseCache[cacheKey];
    }

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
      return factory.getContracts()
        .then(assembleContracts);
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
        },
        remove: function (id) {
          delete (this.items[id]);
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

    /**
     * Remove a contrats from a collection of contracts
     * @param  {Object} contract
     */
    function removeContract (contract) {
      _.remove(contracts, { id: contract.contractId });
      factory.collection.remove(contract.contractId);
    }
  }

  return contractService;
});
