define(['angular', 'services/services'], function (angular, services) {
  'use strict';

  /**
   * @param $http
   * @param $q
   * @returns {Object}
   * @constructor
   */
  function ApiService($http, $q) {

    /**
     * @ngdoc service
     * @name ApiService
     */
    var factory = {};

    ////////////////////
    // Public Members //
    ////////////////////

    /**
     * @ngdoc method
     * @name get
     * @methodOf ApiService
     * @param entityName
     * @param data
     * @param config
     * @returns {*}
     */
    factory.get = function (entityName, data, config) {
      return sendRequest('get', buildData(entityName, data, 'get'), config);
    };

    /**
     * @ngdoc method
     * @name post
     * @methodOf ApiService
     * @param entityName
     * @param data
     * @param action
     * @param config
     * @returns {HttpPromise}
     */
    factory.post = function (entityName, data, action, config) {
      return sendRequest(
        'post',
        buildData(entityName, data, action, true),
        angular.extend({ headers: { 'Content-Type': 'application/x-www-form-urlencoded' } }, config)
      );
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
     * @param method
     * @param url
     * @param data
     * @param config
     * @returns {HttpPromise}
     * @private
     */
    function sendRequest(method, data, config) {
      config = angular.extend({
        method: method,
        url: getApiUrl(),
        cache: true
      }, method === 'post' ? { data: data } : { params: data }, config);

      return $http(config)
        .then(function (response) {
          if (response.is_error) {
            return $q.reject(response);
          }

          return response.data;
        })
        .catch(function (response) {
          return response;
        });
    }

    /**
     * @ngdoc function
     * @param entityName
     * @param data
     * @param action
     * @returns {*}
     * @private
     */
    function buildData(entityName, data, action, stringify) {
      if (!angular.isDefined(entityName)) {
        throw new Error('Entity name not provided');
      }

      if (!angular.isDefined(action)) {
        throw new Error('Action not provided');
      }

      data = angular.extend({
        entity: entityName,
        action: action,
        sequential: 1,
        json: 1,
        rowCount: 0
      }, data);

      // Because data needs to be sent as string for CiviCRM to accept
      return (!!stringify ? jQuery.param(data) : data);
    }

    /**
     * @ngdoc function
     * @returns {string}
     * @private
     */
    function getApiUrl() {
      return '/civicrm/ajax/rest';
    }
  }

  services.factory('ApiService', ['$http', '$q', ApiService]);
});
