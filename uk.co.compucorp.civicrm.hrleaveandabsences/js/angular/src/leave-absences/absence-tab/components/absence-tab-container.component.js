/* eslint-env amd */

(function (CRM) {
  define([
    'leave-absences/absence-tab/modules/components'
  ], function (components) {
    components.component('absenceTabContainer', {
      templateUrl: ['settings', function (settings) {
        return settings.pathTpl + 'components/absence-tab-container.html';
      }],
      controllerAs: 'absence',
      controller: ['$log', '$rootScope', function ($log, $rootScope) {
        $log.debug('Component: absence-tab-container');

        $rootScope.section = 'absence-tab';

        var vm = this;

        vm.contactId = CRM.adminId;
      }]
    });
  });
})(CRM);
