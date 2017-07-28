/* globals CRM */
/* eslint-env amd */

(function (CRM) {
  define([
    'common/modules/services'
  ], function (services) {
    'use strict';

    services.factory('notification', function () {
      var notificationFunctions = {};
      var notificationTypes = ['alert', 'success', 'info', 'error'];

      notificationTypes.forEach(function (notificationType) {
        notificationFunctions[notificationType] = function (title, body, options) {
          return CRM.alert(body, title, notificationType, options);
        };
      });

      return notificationFunctions;
    });
  });
}(CRM));
