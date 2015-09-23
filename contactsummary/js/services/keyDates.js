define(['services/services', 'lodash'], function (services, _) {
  'use strict';

  /**
   * @ngdoc service
   * @name KeyDatesService
   * @returns {{}}
   * @constructor
   */
  function KeyDatesService() {
    var factory = {};
    var data = [];

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc method
     * @name KeyDatesService#get
     * @returns {Array}
     */
    factory.get = function () {
      if (_.isEmpty(data)) {
        data.push({
          label: 'Initial Join Date',
          date: '23/23/23'
        });
        data.push({
          label: 'Contract Start Date',
          date: '23/23/23'
        });
        data.push({
          label: 'Final Termination Date',
          date: '23/23/23'
        });
      }

      return data;
    };

    return factory;

    /////////////////////
    // Private Members //
    /////////////////////

  }

  services.factory('KeyDatesService', [KeyDatesService]);
});