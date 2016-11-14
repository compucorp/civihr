define([
  'common/lodash',
  'job-contract/services/services',
  'job-contract/services/utils'
], function (_, services) {
  'use strict';

  services.factory('ContractRevisionService', [
    '$filter', '$resource', 'settings', '$q', 'UtilsService', '$log',
    function ($filter, $resource, settings, $q, UtilsService, $log) {
      $log.debug('Service: ContractRevisionService');

      /**
       * If parameter passed is a Date object, it converts it into a string
       *
       * @param {Date} dateObj
       * @return {string/any}
       */
      function convertToDateString(dateObj) {
        var dateString = $filter('formatDate')(dateObj, 'YYYY-MM-DD');

        return dateString !== 'Unspecified' ? dateString : dateObj;
      }

      return _.assign($resource(settings.pathRest, {
        action: 'get',
        entity: 'HRJobContractRevision',
        json: {}
      }), {

        /**
         * Validate if a given effective date isn't the equal to any other
         * contract revision effective date for a given contact
         *
         * @param {object} params A list of parameters to pass to the API end-point
         *   which must contain 'contact_id' and 'effective_date'
         * @returns {*}
         */
        validateEffectiveDate: function (params) {
          params.effective_date = convertToDateString(params.effective_date);
          params.sequential = 0;
          params.debug = settings.debug;

          return this.save({
            action: 'validateeffectivedate',
            json: params
          }, null)
          .$promise.then(function (result) {
            return result.values;
          });
        }
      });
  }]);
});
