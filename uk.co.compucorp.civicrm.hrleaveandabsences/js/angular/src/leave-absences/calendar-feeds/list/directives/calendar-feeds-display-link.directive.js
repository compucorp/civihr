/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  DisplayLink.__name = 'calendarFeedsDisplayLink';
  DisplayLink.$inject = ['CalendarFeedsLinkModal'];

  return DisplayLink;

  function DisplayLink (linkModal) {
    return {
      link: DisplayLinkFn,
      restrict: 'C'
    };

    function DisplayLinkFn ($scope, $element, $attr, $ctrl) {
      (function () {
        initEventListeners();
      })();

      /**
       * Listens for click events on target element. When triggered it opens
       * the link modal using the closest parent's title and hash attributes.
       */
      function initEventListeners () {
        $element.on('click', function (event) {
          var title = $element.closest('[data-title]').attr('data-title');
          var hash = $element.closest('[data-hash]').attr('data-hash');

          linkModal.open(title, hash);
        });
      }
    }
  }
});
