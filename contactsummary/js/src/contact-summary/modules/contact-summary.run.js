/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  angular.module('contactsummary.run', ['contactsummary.constants']).run(run);

  run.$inject = ['settings', '$rootScope', '$q', '$log', '$state', '$stateRegistry', '$urlService'];

  function run (settings, $rootScope, $q, $log, $state, $stateRegistry, $urlService) {
    $log.debug('app.run');

    window.delstates = function () {
      console.log('before: ', $state.get());

      $state.get().filter(function (state) {
        return state.name !== '';
      })
        .forEach(function (state) {
          $stateRegistry.deregister(state.name);
        });

      $stateRegistry.deregister('');
      console.log('rules: ', $urlService.rules.rules());
      console.log('after: ', $state.get());
    };

    $rootScope.pathTpl = settings.pathTpl;
    $rootScope.prefix = settings.classNamePrefix;
  }
});
