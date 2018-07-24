/* eslint-env amd */

(function (CRM) {
  define([
    'common/lodash',
    'common/modules/services'
  ], function (_, services) {
    'use strict';

    services.factory('crmAngService', function () {
      return {
        loadForm: loadForm
      };
    });

    /**
     * Opens a CRM form with a provided url and options
     * This is simply a wrapper to CRM.loadForm
     * @see https://docs.civicrm.org/dev/en/latest/framework/ajax
     *
     * @param  {String} url
     * @param  {Object} options
     * @return {Object} extended jQuery object
     */
    function loadForm (url, options) {
      return CRM.loadForm(url, options);
    }
  });
}(CRM));
