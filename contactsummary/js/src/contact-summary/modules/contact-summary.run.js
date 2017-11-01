/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('contactsummary.run', ['contactsummary.constants']).run(run);

  run.$inject = ['settings', '$rootScope', '$q', '$log'];

  function run (settings, $rootScope, $q, $log) {
    $log.debug('app.run');

    $rootScope.pathTpl = settings.pathTpl;
    $rootScope.prefix = settings.classNamePrefix;
  }
});
