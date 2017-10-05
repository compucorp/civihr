/* eslint-env amd */

define([
  'contact-summary/modules/components'
], function (components) {
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
