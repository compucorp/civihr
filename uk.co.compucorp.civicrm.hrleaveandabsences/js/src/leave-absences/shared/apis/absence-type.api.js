/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (_, moment, apis) {
  'use strict';

  apis.factory('AbsenceTypeAPI', ['$log', '$q', 'api', 'shared-settings', function ($log, $q, api, sharedSettings) {
    $log.debug('AbsenceTypeAPI');

    return api.extend({

      /**
       * This method returns all the active AbsenceTypes unless specified in param.
       *
       * @param  {Object} params  matches the api endpoint params (title, weight etc)
       * @param  {Object} additionalParams
       * @return {Promise}
       */
      all: function (params, additionalParams) {
        $log.debug('AbsenceTypeAPI.all');

        var sort;

        params = _.defaults({}, params, { is_active: true });
        sort = _.get(params, 'options.sort') || 'weight ASC';

        return this.getAll('AbsenceType', params, undefined, sort, additionalParams)
          .then(function (data) {
            return data.list;
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
      },

      /**
       * Saves Absence Type
       *
       * @param {Object} params matching the `create()` API endpoint parameters
       */
      save: function (params) {
        return this.sendPOST('AbsenceType', 'create', params)
          .catch($q.reject);
      }
    });
  }]);
});
