/* eslint-env amd */

(function ($) {
  define([
    'common/angular'
  ], function (angular) {
    'use strict';

    angular.module('contactsummary.run', ['contactsummary.constants']).run(run);

    run.$inject = ['settings', '$rootScope', '$q', '$log', '$state', '$stateRegistry', '$urlService'];

    function run (settings, $rootScope, $q, $log, $state, $stateRegistry, $urlService) {
      $log.debug('app.run');

      $rootScope.pathTpl = settings.pathTpl;
      $rootScope.prefix = settings.classNamePrefix;

      addstates();

      $('#mainTabContainer')
        .on('tabsbeforeactivate', function (e, ui) {
          var id = ui.newTab.attr('id');

          if (id !== 'tab_contactsummary') {
            delstates();
          } else {
            addstates();
          }
        });

      function addstates () {
        $urlService.rules.otherwise('/foobarbaz');
        $stateRegistry
          .register({
            name: 'contact-summary',
            url: '/foobarbaz',
            controller: 'ContactSummaryController',
            controllerAs: 'ContactSummaryCtrl',
            templateUrl: settings.pathBaseUrl + settings.pathTpl + 'mainTemplate.html'
          });

        console.log('after addstates: ', $state.get());
      }

      function delstates () {
        $stateRegistry.dispose();
        $urlService.rules.otherwise(function () {});

        console.log('after delstates: ', $state.get());
      }
    }
  });
}(CRM.$));
