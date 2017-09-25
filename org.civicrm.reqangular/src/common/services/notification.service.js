/* eslint-env amd */

(function (CRM) {
  define([
    'common/lodash',
    'common/modules/services'
  ], function (_, services) {
    'use strict';

    services.factory('notificationService', function () {
      return _(['alert', 'success', 'info', 'error'])
        .map(function (type) {
          return [type, function (title, body, options) {
            return CRM.alert(body, title, type, options);
          }];
        })
        .zipObject()
        .value();
    });
  });
}(CRM));
