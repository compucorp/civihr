/* eslint-env amd */

define([
  'leave-absences/shared/modules/apis',
  'common/services/api'
], function (apis) {
  'use strict';

  apis.factory('EntitlementLogAPI', ['$log', 'api', function ($log, api) {
    $log.debug('EntitlementLogAPI');

    return api.extend({

      /**
       * This method returns all the entitlements log.
       * @NOTE This request is not cached.
       *
       * @param  {Object} params - matches the api endpoint parameters.
       * @return {Promise}
       */
      all: function (params) {
        $log.debug('EntitlementLogAPI.all');

        return this.sendGET('LeavePeriodEntitlementLog', 'get', params, false)
          .then(function (data) {
            return data.values;
          });
      }
    });
  }]);
});
