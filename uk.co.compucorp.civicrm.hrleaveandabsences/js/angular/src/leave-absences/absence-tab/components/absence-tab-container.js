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
      controller: ['$log', function ($log) {
        $log.debug('Component: absence-tab-container');

        var vm = Object.create(this);
        vm.contactId = CRM.adminId;

        return vm;
      }]
    });
  });
})(CRM, require);
