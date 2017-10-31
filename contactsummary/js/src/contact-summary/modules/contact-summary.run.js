/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  return angular.module('contactsummary.run', ['contactsummary.constants']).run(['settings', '$rootScope', '$q', '$log',
    function (settings, $rootScope, $q, $log) {
      $log.debug('app.run');

      $rootScope.pathTpl = settings.pathTpl;
      $rootScope.prefix = settings.classNamePrefix;
    }
  ]);
});
