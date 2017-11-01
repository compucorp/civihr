/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  contactService.__name = 'contactService';
  contactService.$inject = ['$log', '$q', 'modelService', 'contactDetailsService', 'contractService'];

  function contactService ($log, $q, Model, ContactDetails, Contract) {
    $log.debug('Service: contactService');

    var factory = Model.createInstance();

    /**
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(contactService|ModelService)} */
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

  return contactService;
});
