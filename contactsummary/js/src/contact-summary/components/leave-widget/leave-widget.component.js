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

  function leaveWidgetController () {
    // console.log('## INIT ##');
  }
});
