/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  return {
    __name: 'calendarFeedsLinkModal',
    bindings: {
      dismiss: '<',
      url: '<'
    },
    controller: _.noop,
    controllerAs: 'linkModal',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sourcePath + 'calendar-feeds/link-modal/components/calendar-feeds-link-modal.html';
    }]
  };
});
