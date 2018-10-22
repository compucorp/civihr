/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/models',
  'common/models/model',
  'common/models/option-group',
  'leave-absences/shared/apis/absence-type.api',
  'leave-absences/shared/instances/absence-type.instance'
], function (_, models) {
  'use strict';

  models.factory('AbsenceType', [
    '$log', '$q', 'Model', 'OptionGroup', 'ABSENCE_TYPE_COLOURS', 'AbsenceTypeAPI', 'AbsenceTypeInstance',
    function ($log, $q, Model, OptionGroup, ABSENCE_TYPE_COLOURS, absenceTypeAPI, instance) {
      $log.debug('AbsenceType');

      return Model.extend({
        /**
         * Calls the all() method of the AbsenceType API, and returns an
         * AbsenceTypeInstance for each absenceType. Also calls
         * optionGroupAPI.valuesOf() to retrieve and set calculation units
         *
         * @param  {Object} params  matches the api endpoint params (title, weight etc)
         * @param  {Object} additionalParams
         * @return {Promise}
         */
        all: function (params, additionalParams) {
          return absenceTypeAPI.all(params, additionalParams)
            .then(function (absenceTypes) {
              return absenceTypes.map(function (absenceType) {
                return instance.init(absenceType, true);
              });
            });
        },

        /**
         * Calls the calculateToilExpiryDate() method of the AbsenceType API
         *
         * @param  {string} absenceTypeID
         * @param  {Object} date
         * @param  {Object} params
         * @return {Promise}
         */
        calculateToilExpiryDate: function (absenceTypeID, date, params) {
          return absenceTypeAPI.calculateToilExpiryDate(absenceTypeID, date, params);
        },

        /**
         * Determines if the absence type can expire by querying if
         * the expiration unit and duration are not null.
         *
         * @param   {string} absenceTypeId
         * @return  {Promise}
         */
        canExpire: function (absenceTypeId) {
          return absenceTypeAPI.all({
            accrual_expiration_unit: { 'IS NOT NULL': 1 },
            accrual_expiration_duration: { 'IS NOT NULL': 1 },
            allow_accruals_request: 1,
            id: absenceTypeId,
            options: { limit: 1 },
            return: ['id']
          })
            .then(function (results) {
              return results.length > 0;
            });
        },

        /**
         * Retrieves yet unused absence type colours
         *
         * @return {Promise} resolves with {Array}
         */
        getAvailableColours: function () {
          return absenceTypeAPI.all(null, { return: ['color'] })
            .then(function (absenceTypes) {
              var usedColours = _.map(absenceTypes, 'color');
              var availableColours = _.difference(ABSENCE_TYPE_COLOURS, usedColours);

              return availableColours.length ? availableColours : ABSENCE_TYPE_COLOURS;
            });
        },

        /**
         * Retrieves calculation units
         * and sets units symbols to provided absence types accordingly
         *
         * Example: if a unit name is "hours", then the symbol will be "h"
         *
         * @param   {Array} absenceTypes array of absence types or their instances
         * @return  {Promise} resolves with the input populated with calculation unit symbols
         */
        loadCalculationUnits: function (absenceTypes) {
          return OptionGroup.valuesOf('hrleaveandabsences_absence_type_calculation_unit')
            .then(function (calculationUnits) {
              calculationUnits = _.keyBy(calculationUnits, 'value');

              return _.map(absenceTypes, function (absenceType) {
                return _.assign(absenceType, {
                  calculation_unit_label: calculationUnits[absenceType.calculation_unit].label,
                  calculation_unit_name: calculationUnits[absenceType.calculation_unit].name
                });
              });
            });
        },

        /**
         * Saves Absence Type
         *
         * @param {Object} params matching the `create()` API endpoint parameters
         */
        save: function (params) {
          return absenceTypeAPI.save(params)
            .catch($q.reject);
        }
      });
    }
  ]);
});
