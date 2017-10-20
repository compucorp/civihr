/* eslint-env amd */

define([
  'common/lodash',
  'job-contract/services/services',
  'job-contract/services/utils'
], function (_, services) {
  'use strict';

  services.factory('ContractLeaveService', ['$resource', '$q', 'settings', 'UtilsService', '$log', 'AbsenceType',
    function ($resource, $q, settings, UtilsService, $log, AbsenceType) {
      $log.debug('Service: ContractLeaveService');

      var ContractLeave = $resource(settings.pathRest, {
        action: 'get',
        entity: 'HRJobLeave',
        json: {}
      });

      /**
       * The API returns values as strings, so we convert them to booleans to
       * make it easy to use them inside conditions
       *
       * @param {Object} values - The values object as returned by the API
       */
      function adjustAddPublicHolidaysValue (values) {
        _.each(values, function (value) {
          value.add_public_holidays = !!parseInt(value.add_public_holidays);
        });
      }

      function getAbsenceTypes () {
        return AbsenceType.all({ options: { sort: 'id ASC' } })
        .then(AbsenceType.loadCalculationUnits)
        .then(function (absenceTypes) {
          return _.indexBy(absenceTypes, 'id');
        });
      }

      return {
        getOne: function (params) {
          var deffered = $q.defer();

          if ((!params || typeof params !== 'object') ||
            (!params.jobcontract_revision_id) ||
            (params.jobcontract_revision_id && typeof +params.jobcontract_revision_id !== 'number') ||
            (params.id && typeof +params.id !== 'number') ||
            (params.leaveType && typeof +params.leaveType !== 'number')) {
            return null;
          }

          params.sequential = 1;
          params.debug = settings.debug;

          ContractLeave.get({
            json: params
          },
          function (data) {
            if (UtilsService.errorHandler(data, 'Unable to fetch contract leave', deffered)) {
              return;
            }

            adjustAddPublicHolidaysValue(data.values);

            deffered.resolve(data.values);
          },
          function () {
            deffered.reject('Unable to fetch contract leave');
          });

          return deffered.promise;
        },
        getOptions: function (fieldName, callAPI) {
          var data;
          var deffered = $q.defer();

          if (!callAPI) {
            data = settings.CRM.options.HRJobLeave || {};

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
          var deffered = $q.defer();
          var crmFields = settings.CRM.fields;

          if (params && typeof params !== 'object') {
            return null;
          }

          if (!params || typeof params !== 'object') {
            params = {};
          }

          if (crmFields && crmFields.HRJobLeave) {
            deffered.resolve(crmFields.HRJobLeave);
          } else {
            params.sequential = 1;

            ContractLeave.get({
              action: 'getfields',
              json: params
            },
            function (data) {
              if (!data.values) {
                deffered.reject('Unable to fetch contract leave fields');
              }

              deffered.resolve(data.values);
            },
            function () {
              deffered.reject('Unable to fetch contract leave fields');
            });
          }

          return deffered.promise;
        },
        save: function (contractLeave) {
          var deffered = $q.defer();
          var params = {
            sequential: 1,
            values: contractLeave,
            debug: settings.debug
          };

          if (!contractLeave || typeof contractLeave !== 'object') {
            return null;
          }

          ContractLeave.save({
            action: 'replace',
            json: params
          },
          null,
          function (data) {
            if (UtilsService.errorHandler(data, 'Unable to create contract leave', deffered)) {
              return;
            }

            adjustAddPublicHolidaysValue(data.values);

            deffered.resolve(data.values);
          },
          function () {
            deffered.reject('Unable to create contract details');
          });

          return deffered.promise;
        },
        model: function (fields, leaveType) {
          var deffered = $q.defer();
          var leaveTypePromise = !leaveType || typeof leaveType !== 'object' ? getAbsenceTypes() : leaveType;

          function createModel (leaveType, fields) {
            var i = 0;
            var len = fields.length;
            var model = [];
            var modelEntry = {};

            for (i; i < len; i++) {
              modelEntry[fields[i].name] = '';
            }

            if (typeof modelEntry.id !== 'undefined') {
              modelEntry.id = null;
            }

            if (typeof modelEntry.jobcontract_revision_id !== 'undefined') {
              modelEntry.jobcontract_revision_id = null;
            }

            if (typeof modelEntry.location !== 'undefined') {
              modelEntry.location = null;
            }

            if ((!leaveType || typeof leaveType !== 'object') ||
              (!modelEntry || typeof modelEntry !== 'object' || typeof modelEntry.leave_type === 'undefined')) {
              return null;
            }

            /**
             * This is a little helper method to calculate the estimated leave amount for a
             * leave type.
             *
             * If this leave type has "add_public_holidays" set, the estimated amount will be
             * the leave amount + the given number of Public Holidays. Otherwise, the estimated
             * amount is the same as the leave amount.
             *
             * @param {number} numberOfPublicHolidays
             * @returns {number}
             */
            modelEntry.getEstimatedLeaveAmount = function (numberOfPublicHolidays) {
              if (this.add_public_holidays && numberOfPublicHolidays) {
                return parseFloat(this.leave_amount) + numberOfPublicHolidays;
              }

              return this.leave_amount;
            };

            _.each(leaveType, function (type, typeId) {
              if (type.calculation_unit_name === 'hours') {
                modelEntry.add_public_holidays = false;
              }

              modelEntry.leave_type = type.id;
              modelEntry.leave_type_title = type.title;
              modelEntry.leave_calculation_unit_name = type.calculation_unit_name;
              modelEntry.leave_calculation_unit_label = type.calculation_unit_label;
              modelEntry.leave_amount = 0;

              model.push(_.cloneDeep(modelEntry));
            });

            return model;
          }

          if (fields) {
            $q.when(leaveTypePromise).then(function (options) {
              deffered.resolve(createModel(options, fields));
            });
          } else {
            this.getFields().then(function (fields) {
              $q.when(leaveTypePromise).then(function (options) {
                deffered.resolve(createModel(options, fields));
              });
            });
          }

          return deffered.promise;
        }
      };
    }
  ]);
});
