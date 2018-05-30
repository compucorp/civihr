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
           * Injects an overlay element to the original element that triggers the tooltip
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
          function makeTooltipClickable () {
            isTouchDevice && injectOverlayToTriggerElement();
            setEventHandlersToTriggerElements();
          }

          /**
           * Defines the handlers that will be used
           * to display the tooltip depending on the device
           */
          function setEventHandlersToTriggerElements () {
            if (isTouchDevice) {
              $overlay.on('touchend', function () {
                toggleTooltip('source', true, 0);
                !scope.open && setEventHandlersToTooltip();
              });
              $document.find('body').on('touchend', function (event) {
                if (event.target === $overlay[0]) {
                  return;
                }

                toggleTooltip('source', false, 0);
                toggleTooltip('tooltip', false, 0);
              });
              $overlay.on('click', function (event) {
                event.stopPropagation();
              });
            } else {
              $element.on('mouseenter', function () {
                toggleTooltip('source', true, 0);
                !scope.open && setEventHandlersToTooltip();
              });
              $element.on('mouseleave', function () {
                toggleTooltip('source', false, 100);
              });
              $element.on('click', function () {
                toggleTooltip('source', false, 0);
                toggleTooltip('tooltip', false, 0);
              });
            }
          }

          /**
           * Sets event handlers to the tooltip itself
           */
          function setEventHandlersToTooltip () {
            var $tooltip;

            $timeout(function () {
              $tooltip = $document.find('.tooltip-clickable-template:visible:last');

              if (!isTouchDevice) {
                $tooltip.on('mouseenter', function () {
                  toggleTooltip('tooltip', true, 0);
                });
                $tooltip.on('mouseleave', function () {
                  toggleTooltip('tooltip', false, 100);
                });
              }

              $tooltip.on(clickType, function () {
                toggleTooltip('source', false, 0);
                toggleTooltip('tooltip', false, 0);
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
           * @param {Number} delay the event handler should be deferred by
           */
          function toggleTooltip (elementType, elementIsHovered, delay) {
            $timeout(function () {
              scope[elementType + '_hovered'] = elementIsHovered;
              scope.open = scope.source_hovered || scope.tooltip_hovered;

              $element.trigger('custom' + (scope.open ? 'Show' : 'Hide'));
              isTouchDevice && $overlay[scope.open ? 'hide' : 'show']();
            }, delay);
          }
        };
      };

      return tooltip;
    };
  }];
});
