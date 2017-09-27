define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (_, moment, apis) {
  'use strict';

  apis.factory('AbsenceTypeAPI', ['$log', 'api', 'shared-settings', function ($log, api, sharedSettings) {
    $log.debug('AbsenceTypeAPI');

    return api.extend({

      /**
       * This method returns all the active AbsenceTypes unless specified in param.
       *
       * @param  {Object} params  matches the api endpoint params (title, weight etc)
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('AbsenceTypeAPI.all');

        return this.sendGET('AbsenceType', 'get', _.defaults(params || {}, { is_active: true }))
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
        $log.debug('AbsenceTypeAPI.calculateToilExpiryDate');

        params = _.assign({}, params, {
          absence_type_id: absenceTypeID,
          date: moment(date).format(sharedSettings.serverDateFormat)
        });

        return this.sendPOST('AbsenceType', 'calculateToilExpiryDate', params)
          .then(function (data) {
            return data.values.expiry_date;
          });
      }
    });
  }]);
});
