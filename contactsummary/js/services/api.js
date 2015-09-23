define(['angular', 'services/services'], function (angular, services) {
  'use strict';

  /**
   * @ngdoc service
   * @name ApiService
   * @param $http
   * @returns {{}}
   * @constructor
   */
  function ApiService($http) {
    var factory = {};

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc method
     * @name ApiService#get
     * @param entityName
     * @param data
     * @returns {*}
     */
    factory.get = function (entityName, data) {
      return post(entityName, data, 'get');
    };

    factory.getValue = function (entityName, data) {
      // todo
    };

    factory.create = function (entityName, data) {
      // todo
    };

    factory.update = function (entityName, data) {
      // todo
    };

    factory.delete = function (entityName, data) {
      // todo
    };

    return factory;

    /////////////////////
    // Private Members //
    /////////////////////

    /**
     * @ngdoc function
     * @param entityName
     * @param data
     * @param action
     * @param cached
     * @returns {*}
     * @private
     */
    function post(entityName, data, action, cached) {
      return sendPost(getApiUrl(), buildPostData(entityName, data, action), buildPostConfig({cached: cached}));
    }

    /**
     * @ngdoc function
     * @param url
     * @param data
     * @param config
     * @returns {HttpPromise}
     * @private
     */
    function sendPost(url, data, config) {
      return $http.post(url, data, config);
    }

    /**
     * @ngdoc function
     * @param entityName
     * @param data
     * @param action
     * @returns {*}
     * @private
     */
    function buildPostData(entityName, data, action) {
      if (!angular.isDefined(entityName)) throw new Error('Entity name not provided');
      if (!angular.isDefined(action)) throw new Error('Action not provided');

      data = data || {};

      angular.extend(data, {entity: entityName, action: action, sequential: 1, json: 1, rowCount: 0});

      // Because data needs to be sent as string for CiviCRM to accept
      return jQuery.param(data);
    }

    /**
     * @ngdoc function
     * @param config
     * @returns {*|{}}
     * @private
     */
    function buildPostConfig(config) {
      config = config || {};

      if (!angular.isObject(config)) throw new TypeError('Config should be of type object');

      if (!angular.isDefined(config.cached)) config.cached = false;

      // Set the headers so AngularJS POSTs the data as form data (and not request payload, which CiviCRM doesn't recognise)
      if (!angular.isDefined(config.headers)) config.headers = {'Content-Type': 'application/x-www-form-urlencoded'};

      return config;
    }

    /**
     * @ngdoc function
     * @returns {string}
     * @private
     */
    function getApiUrl() {
      return '/civicrm/ajax/rest';
      //return CRM.url('civicrm/ajax/rest');
    }
  }

  services.factory('ApiService', ['$http', ApiService]);
});