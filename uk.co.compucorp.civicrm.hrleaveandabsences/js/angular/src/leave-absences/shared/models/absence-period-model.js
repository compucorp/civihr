define([
  'leave-absences/shared/modules/models',
  'common/moment',
  'leave-absences/shared/models/instances/absence-period-instance',
  'leave-absences/shared/apis/absence-period-api',
  'common/models/model',
  'common/services/hr-settings',
], function (models, moment) {
  'use strict';

  models.factory('AbsencePeriod', [
    '$log', 'Model', 'AbsencePeriodAPI', 'AbsencePeriodInstance', 'HR_settings',
    function ($log, Model, absencePeriodAPI, instance, HR_settings) {
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
         * @return Absence period instance or null if not found
         */
        current: function () {
          var dateFormat = HR_settings.DATE_FORMAT.toUpperCase();
          var checkDate = moment().format(dateFormat);

          var params = {
            "start_date": {
              '<=': checkDate
            },
            "end_date": {
              '>=': checkDate
            }
          }

          return absencePeriodAPI.all(params)
            .then(function (absencePeriods) {
              if (absencePeriods.length) {
                return instance.init(absencePeriods[0], true);
              }
              return null;
            });
        }
      });
    }
  ]);
});
