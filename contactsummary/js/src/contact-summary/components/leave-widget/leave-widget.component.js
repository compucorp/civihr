/* eslint-env amd */

define([
  'leave-absences/shared/models/absence-type.model',
  'contact-summary/modules/components'
], function (AbsenceType, components) {
  console.log('....', AbsenceType);
  components.component('leaveWidget', {
    controller: leaveWidgetController,
    controllerAs: 'leaveWidget',
    templateUrl: ['settings', function (settings) {
      return settings.pathBaseUrl + settings.pathTpl + 'components/leave-widget/leave-widget.html';
    }]
  });

  leaveWidgetController.$inject = ['$log'];

  function leaveWidgetController ($log) {
    $log.debug('Controller: leaveWidgetController');
  }
});
