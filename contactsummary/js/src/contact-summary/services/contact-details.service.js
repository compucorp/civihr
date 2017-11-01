/* eslint-env amd */

define([
  'common/lodash',
  'common/moment'
], function (_, moment) {
  'use strict';

  ContactDetailsService.__name = 'ContactDetailsService';
  ContactDetailsService.$inject = ['$q', '$log', 'ApiService', 'ModelService', 'settings'];

  function ContactDetailsService ($q, $log, Api, Model, settings) {
    $log.debug('Service: ContactDetailsService');

    /**
     * @ngdoc service
     * @name ContactDetailsService
     */
    var factory = Model.createInstance();

    /**
     * @ngdoc method
     * @name get
     * @methodOf ContactDetailsService
     * @this ContactDetailsService
     * @returns {*}
     */
    factory.get = function () {
      /** @type {(ContactDetailsService|ModelService)} */
      var self = this;
      var deferred = $q.defer();

      init().then(function () {
        deferred.resolve(self.getData());
      });

      return deferred.promise;
    };

    function init () {
      var deferred = $q.defer();

      if (_.isEmpty(factory.getData())) {
        var contactId = settings.contactId;

        Api
          .get('Contact', {contact_id: contactId, return: 'birth_date'})
          .then(function (response) {
            if (response.values.length === 0) {
              throw new Error('Contact with ID ' + contactId + ' not found');
            }

            var dob = response.values[0].birth_date;
            var age = moment(dob, 'YYYY-MM-DD').isValid()
            ? calculateAge(dob)
            : '';

            factory.setDataKey('id', contactId);
            factory.setDataKey('dateOfBirth', dob);
            factory.setDataKey('age', age);

            deferred.resolve();
          })
          .catch(function (response) {
            deferred.reject(response);
          });
      } else {
        deferred.resolve();
      }

      return deferred.promise;
    }

    /**
     * Calculate age from birth date
     *
     * @param {string} dateOfBirth Date of birth in a YYYY-MM-DD format
     * @returns {string}
     */
    function calculateAge (dateOfBirth) {
      return moment().diff(moment(dateOfBirth, 'YYYY-MM-DD'), 'years');
    }

    return factory;
  }

  return ContactDetailsService;
});
