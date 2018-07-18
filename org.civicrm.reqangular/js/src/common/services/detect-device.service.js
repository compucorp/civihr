/* eslint-env amd */

define([
  'common/modules/services'
], function (services) {
  'use strict';

  /**
   * This service is used to detect different device types
   */
  services.factory('detectDevice', [function () {
    return {
      /**
       * Detects if the the device is mobile
       *
       * @return {Boolean}
       */
      isMobile: function () {
        return document.getElementsByTagName('body')[0].classList.contains('mobile');
      }
    };
  }
  ]);
});
