/* eslint-env amd */

define([
  'leave-absences/shared/modules/components',
  'leave-absences/shared/modules/shared-settings'
], function (components) {
  components.component('leaveWidget', {
    controller: leaveWidgetController,
    controllerAs: 'leaveWidget',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-widget/leave-widget.html';
    }]
  });

  leaveWidgetController.$inject = ['$log'];

  function leaveWidgetController ($log) {
    $log.debug('Controller: leaveWidgetController');
  }
});
