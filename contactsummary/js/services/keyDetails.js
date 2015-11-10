define([
  'lodash',
  'moment',
  'modules/services',
  'modules/settings',
  'services/api'
], function (_, moment, services) {
  'use strict';

  /**
   * @ngdoc service
   * @name KeyDetailsService
   * @param $log
   * @param {$q} $q
   * @param {ApiService} Api
   * @param {settings} settings
   * @returns {{}}
   * @constructor
   */
  function KeyDetailsService($log, $q, Api, settings) {
    $log.debug('Service: KeyDetailsService');

    var factory = {};
    var contractId;
    var contractRevisionId;

    factory.data = {};

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc method
     * @name KeyDetailsService#get
     * @returns {{}|*|Array}
     */
    factory.get = function () {
      var deferred = $q.defer();

      if (_.isEmpty(factory.data)) {
        // todo: cache it
        Api.get('Contact', {contact_id: settings.contactId, return: 'birth_date'})
          .then(getContract)
          .then(getContractRevision)
          .then(getJobDetails)
          .then(getJobHour)
          .then(getJobPay)
          .then(function (response) {
            $log.debug(response);

            if (response.values.length === 0) {
              throw new Error('Job pay for revision with ID ' + contractRevisionId + ' not found');
            }

            factory.data.grossAnnualPay = response.values[0].pay_currency + ' ' + response.values[0].pay_amount;
          })
          .then(function () {
            deferred.resolve(factory.data);
          })
          .catch(function (response) {
            $log.debug('Something went wrong in KeyDetails');
            deferred.reject(response);
          });
      }

      return deferred.promise;
      //return factory.data;
    };

    return factory;

    /////////////////////
    // Private Members //
    /////////////////////

    function getContract(response) {
      $log.debug(response);

      if (response.values.length === 0) throw new Error('Contact with ID ' + settings.contactId + ' not found');

      factory.data.dateOfBirth = response.values[0].birth_date;

      factory.data.age = moment
        .duration(moment() - moment(factory.data.dateOfBirth, 'YYYY-MM-DD'), 'milliseconds')
        .humanize();

      return Api.get('HRJobContract', {contact_id: settings.contactId, is_primary: 1});
    }

    function getContractRevision(response) {
      $log.debug(response);

      if (response.values.length === 0) {
        throw new Error('Primary contract for contact with ID ' + settings.contactId + ' not found');
      }

      contractId = response.values[0].id;

      return Api.get('HRJobContractRevision', {jobcontract_id: contractId});
    }

    function getJobDetails(response) {
      $log.debug(response);

      if (response.values.length === 0) {
        throw new Error('Contract revision for contract with ID ' + contractId + ' not found');
      }

      contractRevisionId = response.values[0].id;

      return Api.get('HRJobDetails', {jobcontract_revision_id: contractRevisionId});
    }

    function getJobHour(response) {
      $log.debug(response);

      if (response.values.length === 0) {
        throw new Error('Job details for revision with ID ' + contractRevisionId + ' not found');
      }

      factory.data.contractType = response.values[0].contract_type;

      return Api.get('HRJobHour', {jobcontract_revision_id: contractRevisionId});
    }

    function getJobPay(response) {
      $log.debug(response);

      if (response.values.length === 0) {
        throw new Error('Job hours for revision with ID ' + contractRevisionId + ' not found');
      }

      factory.data.hours = response.values[0].hours_amount + ' per ' + response.values[0].hours_unit;

      return Api.get('HRJobPay', {jobcontract_revision_id: contractRevisionId});
    }
  }

  services.factory('KeyDetailsService', ['$log', '$q', 'ApiService', 'settings', KeyDetailsService]);

});
