/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  API.__name = 'API';
  API.$inject = ['$resource', '$q', 'settings', '$log'];

  function API ($resource, $q, settings, $log) {
    $log.debug('Service: UtilsService');

    return {
      resource: function (entity, action, json) {
        if ((!entity || typeof entity !== 'string') ||
          (!action || typeof action !== 'string') ||
          (json && typeof json !== 'object')) {
          return null;
        }

        return $resource(settings.pathRest, {
          action: action,
          entity: entity,
          json: json
        });
      },
      getOne: function (entity, params) {
        if ((!entity || typeof entity !== 'string') ||
          (params && typeof params !== 'object')) {
          return null;
        }

        var deffered = $q.defer();
        var json = angular.extend({
          sequential: 1
        }, params);
        var val;

        this.resource(entity, 'get', json).get(function (data) {
          val = data.values;
          deffered.resolve(val.length === 1 ? val[0] : null);
        }, function () {
          deffered.reject('Unable to fetch data');
        });

        return deffered.promise;
      },
      get: function (entity, params) {
        if ((!entity || typeof entity !== 'string') ||
          (params && typeof params !== 'object')) {
          return null;
        }

        var deffered = $q.defer();
        var json = angular.extend({
          sequential: 1
        }, params);

        this.resource(entity, 'get', json).get(function (data) {
          deffered.resolve(data.values);
        }, function () {
          deffered.reject('Unable to fetch data');
        });

        return deffered.promise;
      }
    };
  }

  return API;
});
