/* eslint-env amd */

define([
  'common/angular',
  'common/lodash'
], function (angular, _) {
  'use strict';

  jobRoleService.__name = 'jobRoleService';
  jobRoleService.$inject = ['$q', '$log', 'apiService', 'modelService', 'contractService'];

  function jobRoleService ($q, $log, Api, Model, Contract) {
    $log.debug('Service: jobRoleService');

    var factory = {};

    factory.collection = {
      items: {},
      insertItem: function (key, item) {
        this.items[key] = item;
      },
      getItem: function (key) {
        return this.items[key];
      },
      set: function (collection) {
        this.items = collection;
      },
      get: function () {
        return this.items;
      }
    };
    factory.get = get;
    factory.getCollection = getCollection;

    return factory;

    /**
     * @returns {*}
     */
    function get () {
      return init().then(function () {
        return factory.getCollection();
      });
    }

    function getCollection () {
      return factory.collection.get();
    }

    function init () {
      var deferred = $q.defer();

      Contract.get().then(function (response) {
        var contractIds = [];

        angular.forEach(response, function (contract) {
          contractIds.push(contract.id);
        });

        if (contractIds.length === 0) {
          return $q.reject('No job roles found for contracts');
        }

        Api.post('HrJobRoles', {job_contract_id: {'IN': contractIds}}, 'get')
        .then(function (response) {
          var roles = response.values.map(function (role) {
            return {
              id: role.id,
              title: role.title,
              department: role.department,
              status: role.status,
              start_date: role.start_date,
              end_date: role.end_date
            };
          });

          factory.collection.set(roles);
        })
        .finally(function () {
          deferred.resolve();
        });
      });

      return deferred.promise;
    }
  }

  return jobRoleService;
});
