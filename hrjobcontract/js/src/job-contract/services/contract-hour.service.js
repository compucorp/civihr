/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  contractHourService.__name = 'contractHourService';
  contractHourService.$inject = [
    '$resource', 'settings', '$q', 'utilsService', '$log'
  ];

  function contractHourService ($resource, settings, $q, utilsService, $log) {
    $log.debug('Service: contractHourService');

    var ContractHour = $resource(settings.pathRest, {
      action: 'get',
      entity: 'HRJobHour',
      json: {}
    });

    return {
      getOne: function (params) {
        if ((!params || typeof params !== 'object') ||
          (!params.jobcontract_revision_id) ||
          (params.jobcontract_revision_id && typeof +params.jobcontract_revision_id !== 'number')) {
          return null;
        }

        params.sequential = 1;
        params.debug = settings.debug;

        var deffered = $q.defer();
        var val;

        ContractHour.get({
          json: params
        },
        function (data) {
          if (utilsService.errorHandler(data, 'Unable to fetch contract hours', deffered)) {
            return;
          }

          val = data.values;
          deffered.resolve(val.length === 1 ? val[0] : null);
        },
        function () {
          deffered.reject('Unable to fetch contract hours');
        });

        return deffered.promise;
      },
      getOptions: function (fieldName, callAPI) {
        var deffered = $q.defer();
        var data;

        if (!callAPI) {
          data = settings.CRM.options.HRJobHour || {};

          if (fieldName && typeof fieldName === 'string') {
            data = data[fieldName];
          }

          deffered.resolve(data || {});
        } else {
          // TODO call2API
        }

        return deffered.promise;
      },
      getFields: function (params) {
        if (params && typeof params !== 'object') {
          return null;
        }

        if (!params || typeof params !== 'object') {
          params = {};
        }

        var deffered = $q.defer();
        var crmFields = settings.CRM.fields;

        if (crmFields && crmFields.HRJobHour) {
          deffered.resolve(crmFields.HRJobHour);
        } else {
          params.sequential = 1;

          ContractHour.get({
            action: 'getfields',
            json: params
          },
          function (data) {
            if (!data.values) {
              deffered.reject('Unable to fetch contract hours fields');
            }

            deffered.resolve(data.values);
          },
          function () {
            deffered.reject('Unable to fetch contract hours fields');
          });
        }

        return deffered.promise;
      },
      save: function (contractHour) {
        if (!contractHour || typeof contractHour !== 'object') {
          return null;
        }

        var deffered = $q.defer();
        var params = angular.extend({
          sequential: 1,
          debug: settings.debug
        }, contractHour);
        var val;

        ContractHour.save({
          action: 'create',
          json: params
        },
        null,
        function (data) {
          if (utilsService.errorHandler(data, 'Unable to create contract hours', deffered)) {
            return;
          }

          val = data.values;
          deffered.resolve(val.length === 1 ? val[0] : null);
        },
        function () {
          deffered.reject('Unable to create contract hours');
        });

        return deffered.promise;
      },
      model: function (fields) {
        var deffered = $q.defer();

        function createModel (fields) {
          var i = 0;
          var len = fields.length;
          var model = {};

          for (i; i < len; i++) {
            model[fields[i].name] = '';
          }

          if (typeof model.id !== 'undefined') {
            model.id = null;
          }

          if (typeof model.jobcontract_revision_id !== 'undefined') {
            model.jobcontract_revision_id = null;
          }

          return model;
        }

        if (fields) {
          deffered.resolve(createModel(fields));
        } else {
          this.getFields().then(function (fields) {
            deffered.resolve(createModel(fields));
          });
        }

        return deffered.promise;
      }
    };
  }

  return contractHourService;
});
