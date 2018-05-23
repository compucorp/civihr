/* eslint-env amd */

define([
  'common/angular',
  'common/angularBootstrap'
], function (angular) {
  'use strict';

  var $document, $timeout;

  return ['$delegate', '$document', '$timeout', function ($delegate, _$document_, _$timeout_) {
    $document = _$document_;
    $timeout = _$timeout_;

    return function () {
      var tooltip = $delegate.apply(this, arguments);
      var compilator = tooltip.compile;
      var isTouchDevice = 'ontouchstart' in $document[0].documentElement;
      var clickType = isTouchDevice ? 'touchend' : 'click';

      tooltip.compile = function (tElement, tAttr) {
        return function link (scope, $element, attributes, tooltipCtrl) {
          var $overlay;
          var isTooltipClickable = $element.attr('uib-tooltip-clickable');

          isTooltipClickable && setInitialAttributesForClickableTooltip();
          compilator(tElement, tAttr)(scope, $element, attributes, tooltipCtrl);
          isTooltipClickable && makeTooltipClickable();

          /**
           * Injects an overlay element to the original tooltip trigger element
           */
          function injectOverlayToTriggerElement () {
            $overlay = angular.element('<div class="tooltip-overlay"></div>');

            $element.css({ 'position': 'relative' });
            $overlay.css({
              position: 'absolute',
              height: '100%',
              width: '100%',
              'z-index': 1,
              top: 0,
              left: 0
            });
            $element.append($overlay);
          }

          /**
           * Makes the tooltip clickable
           * by making it hoverable on non-touch devices
           * and dismissable on touch devices
           */
          function makeTooltipClickable (attributes) {
            isTouchDevice && injectOverlayToTriggerElement();
            setEventHandlersToTriggerElements();
          }

          /**
           * Sets handlers to the tooltip trigger elements
           */
          function setEventHandlersToTriggerElements () {
            if (isTouchDevice) {
              $overlay.on('touchend', function (event) {
                toggleTooltip('source', true, false, event);
                !scope.open && setEventHandlersToTooltip();
              });
              $document.find('body').on('touchend', function () {
                toggleTooltip('source', false, false);
                toggleTooltip('tooltip', false, false);
              });
              $overlay.on('click', function (event) {
                event.stopPropagation();
              });
            } else {
              $element.on('mouseenter', function () {
                toggleTooltip('source', true, false);
                !scope.open && setEventHandlersToTooltip();
              });
              $element.on('mouseleave', function () {
                toggleTooltip('source', false, true);
              });
            }

            $element.on(clickType, function () {
              toggleTooltip('source', false, false);
              toggleTooltip('tooltip', false, false);
            });
          }

          /**
           * Sets event handlers to the tooltip itself
           */
          function setEventHandlersToTooltip () {
            var $tooltip;

            $timeout(function () {
              $tooltip = $document.find('.tooltip-clickable-template:visible:last');

              $tooltip.on('mouseenter', function () {
                toggleTooltip('tooltip', true, false);
              });
              $tooltip.on('mouseleave', function () {
                toggleTooltip('tooltip', false, true);
              });
              $tooltip.on(clickType, function () {
                toggleTooltip('source', false, false);
                toggleTooltip('tooltip', false, false);
              });
            });
          }

          /**
           * Sets initial attributes for clickable tooltip
           */
          function setInitialAttributesForClickableTooltip () {
            attributes.tooltipTrigger = 'customShow';
            attributes.tooltipAnimation = false;
          }

          /**
           * Shows/hides tooltip
           *
           * @param {String} elementType source|tooltip
           * @param {Boolean} elementIsHovered is element currently hovered or not
           * @param {Boolean} delayEvent should the event be delayed
           * @param {Event} eventToNotBePropagated if passed, it will not be propagated
           */
          function toggleTooltip (elementType, elementIsHovered, delayEvent, eventToNotBePropagated) {
            var timeout = delayEvent ? 100 : 0;

            $timeout(function () {
              scope[elementType + '_hovered'] = elementIsHovered;
              scope.open = scope.source_hovered || scope.tooltip_hovered;

              $element.trigger('custom' + (scope.open ? 'Show' : 'Hide'));
              isTouchDevice && $overlay[scope.open ? 'hide' : 'show']();
            }, timeout);

            if (eventToNotBePropagated) {
              eventToNotBePropagated.stopPropagation();
              eventToNotBePropagated.stopImmediatePropagation();
            }
          }
        };
      };

      return tooltip;
    };
  }];
});
