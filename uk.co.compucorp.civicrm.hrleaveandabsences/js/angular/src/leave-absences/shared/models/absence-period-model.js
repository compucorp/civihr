/* eslint-env amd */

define([
  'leave-absences/shared/modules/models',
  'common/moment',
  'leave-absences/shared/modules/shared-settings',
  'leave-absences/shared/instances/absence-period.instance',
  'leave-absences/shared/apis/absence-period-api',
  'common/models/model',
  'common/services/hr-settings'
], function (models, moment) {
  'use strict';

  models.factory('AbsencePeriod', [
    '$log', 'Model', 'AbsencePeriodAPI', 'AbsencePeriodInstance', 'shared-settings',
    function ($log, Model, absencePeriodAPI, instance, sharedSettings) {
      $log.debug('AbsencePeriod');

      return Model.extend({
        /**
         * Calls the all() method of the AbsencePeriod API, and returns an
         * AbsencePeriodInstance for each absencePeriod.
         *
         * @param  {Object} params  matches the api endpoint params (title, start_date, end_date etc)
         * @return {Promise}
         */
        all: function (params) {
          return absencePeriodAPI.all(params)
            .then(function (absencePeriods) {
              return absencePeriods.map(function (absencePeriod) {
                return instance.init(absencePeriod, true);
              });
            });
        },
        /**
         *  Finds out if current date is in any absence period.
         *  If found then return absence period instance of it.
         *
         * @return {Object} Absence period instance or null if not found
         */
        current: function () {
          var today = moment().format(sharedSettings.serverDateFormat);

          var params = {
            'start_date': {
              '<=': today
            },
            'end_date': {
              '>=': today
            }
          };

          return absencePeriodAPI.all(params)
            .then(function (absencePeriods) {
              if (absencePeriods && absencePeriods.length) {
                return instance.init(absencePeriods[0], true);
              }

              return null;
            });
        }
      });
    }
  ]);
});
