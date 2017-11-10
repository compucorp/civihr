/* eslint-env amd */

(function ($) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    apiService.__name = 'apiService';
    apiService.$inject = ['$http', '$q'];

    function apiService ($http, $q) {
      return {
        get: get,
        post: post
      };

      /**
       * @param entityName
       * @param data
       * @param action
       * @param stringify
       * @returns {*}
       */
      function buildData (entityName, data, action, stringify) {
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
        return (stringify ? $.param(data) : data);
      }

      /**
       * @param entityName
       * @param data
       * @param config
       * @returns {*}
       */
      function get (entityName, data, config) {
        return sendRequest('get', buildData(entityName, data, 'get'), config);
      }

      /**
       * @param entityName
       * @param data
       * @param action
       * @param config
       * @returns {HttpPromise}
       */
      function post (entityName, data, action, config) {
        config = angular.extend({
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
        }, config);

        return sendRequest('post', buildData(entityName, data, action, true), config);
      }

      /**
       * @param method
       * @param data
       * @param config
       * @returns {HttpPromise}
       */
      function sendRequest (method, data, config) {
        config = angular.extend({
          method: method,
          url: '/civicrm/ajax/rest'
        }, (method === 'post' ? { data: data } : { params: data }), config);

        return $http(config)
          .then(function (response) {
            return response.is_error ? $q.reject(response) : response.data;
          })
          .catch(function (response) {
            return response;
          });
      }
    }

    return apiService;
  });
}(CRM.$));
