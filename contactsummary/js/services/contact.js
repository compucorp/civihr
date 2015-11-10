define([
  'lodash',
  'modules/services',
  'services/model',
  'services/leave',
  'services/contactDetails',
  'services/contract'
], function (_, services) {
  'use strict';

  /**
   * @param {ModelService} Model
   * @param ContactDetails
   * @param {LeaveService} Leave
   * @param {ContractService} Contract
   * @param $q
   * @param $log
   * @returns {Object}
   * @constructor
   */
  function ContactService($log, $q, Model, ContactDetails, Leave, Contract) {
    $log.debug('Service: ContactService');

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc service
     * @name ContactService
     */
    var factory = Model.createInstance();

    /**
     * @ngdoc method
     * @name get
     * @methodOf ContactService
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(ContactService|ModelService)} */
      var self = this;

      return init().then(function () {
        return self.getData();
      });
    };

    return factory;

    /////////////////////
    // Private Members //
    /////////////////////

    function init() {
      var deferred = $q.defer();

      if (_.isEmpty(factory.getData())) {
        initContactDetails()
          .then(initContract)
          .then(initLeave)
          .then(function () {
            deferred.resolve();
          });
      } else {
        deferred.resolve();
      }

      return deferred.promise;
    }

    function initContactDetails() {
      return ContactDetails.get()
        .then(function (response) {
          factory.setDataKey('id', response.id);
          factory.setDataKey('dateOfBirth', response.dateOfBirth);
          factory.setDataKey('age', response.age);
        });
    }

    function initContract() {
      return Contract.get()
        .then(function (response) {
          factory.setDataKey('contract', response);
        });
    }

    function initLeave() {
      return Leave.get()
        .then(function (response) {
          factory.setDataKey('leave', response);
        });
    }
  }

  services.factory('ContactService', ['$log', '$q', 'ModelService', 'ContactDetailsService', 'LeaveService', 'ContractService', ContactService]);
});
