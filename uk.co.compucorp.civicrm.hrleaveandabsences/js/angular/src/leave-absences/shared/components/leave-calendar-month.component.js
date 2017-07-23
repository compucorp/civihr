/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('leaveCalendarMonth', {
    bindings: {
      month: '<',
      contacts: '<',
      showContactName: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-calendar-month.html';
    }],
    controllerAs: 'month',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: leave-calendar-month');
  }
});
