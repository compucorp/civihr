define([
  'common/lodash',
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (_, apis) {
  'use strict';

  apis.factory('AbsenceTypeAPI', ['$log', 'api', function ($log, api) {
    $log.debug('AbsenceTypeAPI');

    return api.extend({

      /**
       * This method returns all the AbsenceTypes.
       *
       * @param  {Object} params  matches the api endpoint params (title, weight etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('AbsenceTypeAPI');

        return this.sendGET('AbsenceType', 'get', params)
          .then(function (data) {
            return data.values;
          });
      },

      /**
       * Calculate Toil Expiry Date
       *
       * @param  {string} absenceTypeID
       * @param  {Object} date
       * @param  {Object} params
       * @return {Promise}
       */
      calculateToilExpiryDate: function (absenceTypeID, date, params) {
        $log.debug('calculateToilExpiryDate');

        params = params ? params : {};
        params = _.assign(params, {
          absence_type_id: absenceTypeID,
          date: date
        });

        return this.sendPOST('AbsenceType', 'calculateToilExpiryDate', params)
          .then(function (data) {
            return data.values.expiry_date;
          });
      }
    });
  }]);
});
