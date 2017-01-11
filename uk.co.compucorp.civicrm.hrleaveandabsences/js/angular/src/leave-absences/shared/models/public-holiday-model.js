define([
  'leave-absences/shared/modules/models',
  'common/moment',
  'leave-absences/shared/models/instances/public-holiday-instance',
  'leave-absences/shared/apis/public-holiday-api',
  'common/models/model',
  'common/services/hr-settings',
], function (models, moment) {
  'use strict';

  models.factory('PublicHoliday', [
    '$log', 'Model', 'PublicHolidayAPI', 'PublicHolidayInstance', 'HR_settings',
    function ($log, Model, publicHolidayAPI, instance, HR_settings) {
      $log.debug('PublicHoliday');

      return Model.extend({
        /**
         * Calls the all() method of the PublicHoliday API, and returns an
         * PublicHolidayInstance for each public holiday.
         *
         * @param  {Object} params  matches the api endpoint params (title, date etc)
         * @return {Promise}
         */
        all: function (params) {
          $log.debug('PublicHoliday.all', params);

          return publicHolidayAPI.all(params)
            .then(function (publicHolidays) {
              return publicHolidays.map(function (publicHoliday) {
                return instance.init(publicHoliday, true);
              });
            });
        },
        /**
         *  Finds out if given date is a public holiday.
         *
         * @param  {Date} whichDate given date either as Date object or its string representation
         * @return {Bool} returns true if date is a public holday else false
         */
        isPublicHoliday: function (whichDate) {
          $log.debug('PublicHoliday.isPublicHoliday', whichDate);

          var checkDate = moment(whichDate).format('YYYY-MM-DD');
          var params = {
            'date': checkDate
          };

          return publicHolidayAPI.all(params)
            .then(function (publicHolidays) {

              if (!!publicHolidays.length) {
                return true;
              }

              return false;
            });
        }
      });
    }
  ]);
});
