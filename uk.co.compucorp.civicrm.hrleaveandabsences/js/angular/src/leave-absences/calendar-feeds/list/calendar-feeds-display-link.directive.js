/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  DisplayLink.__name = 'calendarFeedsDisplayLink';
  DisplayLink.$inject = ['CalendarFeedsLinkModal'];

  return DisplayLink;

  function DisplayLink (linkModal) {
    return {
      controller: _.noop,
      controllerAs: 'displayLink',
      link: DisplayLinkFn,
      restrict: 'C'
    };

    function DisplayLinkFn ($scope, $element, $attr, $ctrl) {
      (function () {
        initEventListeners();
      })();

      /**
       * Listens for click events on target element. When triggered it opens
       * the link modal using the parent's hash attribute.
       */
      function initEventListeners () {
        $element.on('click', function (event) {
          var hash = $element.parents('[data-hash]').attr('data-hash');

          linkModal.open(hash);
        });
      }
    }
  }
});
