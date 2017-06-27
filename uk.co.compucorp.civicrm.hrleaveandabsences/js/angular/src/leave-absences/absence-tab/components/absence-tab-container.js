/* eslint-env amd */

(function (CRM) {
  define([
    'leave-absences/absence-tab/modules/components'
  ], function (components) {
    components.component('absenceTabContainer', {
      bindings: {
        contactId: '<'
      },
      templateUrl: ['settings', function (settings) {
        return settings.pathTpl + 'components/absence-tab-container.html';
      }],
      controllerAs: 'absence',
      controller: ['$log', '$rootScope', function ($log, $rootScope) {
        $log.debug('Component: absence-tab-container');

        $rootScope.role = 'admin';

        var vm = Object.create(this);

        vm.adminId = CRM.adminId;

        return vm;
      }]
    });
  });
})(CRM, require);
