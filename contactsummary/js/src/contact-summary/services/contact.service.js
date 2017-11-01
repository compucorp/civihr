/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  ContactService.__name = 'ContactService';
  ContactService.$inject = ['$log', '$q', 'ModelService', 'ContactDetailsService', 'ContractService'];

  function ContactService ($log, $q, Model, ContactDetails, Contract) {
    $log.debug('Service: ContactService');

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

    function init () {
      var deferred = $q.defer();

      if (_.isEmpty(factory.getData())) {
        initContactDetails()
          .then(initContract)
          .then(function () {
            deferred.resolve();
          });
      } else {
        deferred.resolve();
      }

      return deferred.promise;
    }

    function initContactDetails () {
      return ContactDetails.get()
        .then(function (response) {
          factory.setDataKey('id', response.id);
          factory.setDataKey('dateOfBirth', response.dateOfBirth);
          factory.setDataKey('age', response.age);
        });
    }

    function initContract () {
      return Contract.get()
      .then(function (response) {
        factory.setDataKey('contract', response);
      });
    }
  }

  return ContactService;
});
