/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  contractRevisionService.__name = 'contractRevisionService';
  contractRevisionService.$inject = [
    '$filter', '$resource', 'settings', '$q', 'utilsService', '$log'
  ];

  function contractRevisionService ($filter, $resource, settings, $q,
    utilsService, $log) {
    $log.debug('Service: contractRevisionService');

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

    /**
     * If parameter passed is a Date object, it converts it into a string
     *
     * @param {Date} dateObj
     * @return {string/any}
     */
    function convertToDateString (dateObj) {
      var dateString = $filter('formatDate')(dateObj, 'YYYY-MM-DD');

      return dateString !== 'Unspecified' ? dateString : dateObj;
    }
  }

  return contractRevisionService;
});
