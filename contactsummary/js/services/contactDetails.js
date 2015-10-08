define(['services/services', 'moment', 'services/api', 'lodash'], function (services, moment) {
  'use strict';

  /**
   *
   * @param Api
   * @param {ModelService} Model
   * @param settings
   * @param $q
   * @param $log
   * @returns {*|Object|ModelService}
   * @constructor
   */
  function ContactDetailsService(Api, Model, settings, $q, $log) {
    $log.debug('Service: ContactDetailsService');

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc service
     * @name ContactDetailsService
     */
    var factory = Model.createInstance();

    /**
     * @this ContactDetailsService
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(ContactDetailsService|ModelService)} */
      var self = this;
      var deferred = $q.defer();

      init().finally(function () {
        deferred.resolve(self.getData());
      });

      return deferred.promise;
    };

    /////////////////////
    // Private Members //
    /////////////////////

    function init() {
      var deferred = $q.defer();

      if (_.isEmpty(factory.getData())) {
        var contactId = settings.contactId;

        factory.setDataKey('id', contactId);

        Api.get('Contact', {contact_id: contactId, return: 'birth_date'})
          .then(function (response) {
            if (response.values.length === 0) throw new Error('Contact with ID ' + contactId + ' not found');

            var dob = response.values[0].birth_date,
              age = moment(moment(dob, 'YYYY-MM-DD')).fromNow(true);

            factory.setDataKey('dateOfBirth', dob);
            factory.setDataKey('age', age);

            deferred.resolve();
          })
          .catch(function (response) {
            deferred.reject(response);
          });
      }

      return deferred.promise;
    }


    return factory;
  }

  services.factory('ContactDetailsService', ['ApiService', 'ModelService', 'settings', '$q', '$log', ContactDetailsService]);
});
