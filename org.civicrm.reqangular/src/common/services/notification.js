(function (CRM) {
  define([
    'common/modules/services'
  ], function (services) {
    'use strict';

    services.factory('notification', function () {
      return {

        /**
         * Uses CRM.alert to display alert notification
         *
         * @param {string} title
         * @param {string} body
         * @param {object} options
         */
        alert: function (title, body, options) {
          return CRM.alert(body, title, 'alert', options);
        },

        /**
         * Uses CRM.alert to display success notification
         *
         * @param {string} title
         * @param {string} body
         * @param {object} options
         */
        success: function (title, body, options) {
          return CRM.alert(body, title, 'success', options);
        },

        /**
         * Uses CRM.alert to display info notification
         *
         * @param {string} title
         * @param {string} body
         * @param {object} options
         */
        info: function (title, body, options) {
          return CRM.alert(body, title, 'info', options);
        },

        /**
         * Uses CRM.alert to display error notification
         *
         * @param {string} title
         * @param {string} body
         * @param {object} options
         */
        error: function (title, body, options) {
          return CRM.alert(body, title, 'error', options);
        }
      }
    })
  });
}(CRM));
