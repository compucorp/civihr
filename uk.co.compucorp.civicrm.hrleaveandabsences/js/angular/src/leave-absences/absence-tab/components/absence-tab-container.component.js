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
      controller: AbsenceTabContainerController
    });

    AbsenceTabContainerController.$inject = ['$log', '$rootScope', 'DateFormat'];

    function AbsenceTabContainerController ($log, $rootScope, DateFormat) {
      $log.debug('Component: absence-tab-container');

      $rootScope.section = 'absence-tab';

      var vm = this;

      vm.contactId = CRM.adminId;

      (function init () {
        // @NOTE this is a temporary solution that sets date format from CRM
        // to HRSettings. This should have been done in the config.js file
        // however Absence Tab has issues with routing so it isn't possible now.
        DateFormat.getDateFormat();
      })();
    }
  });
})(CRM);
