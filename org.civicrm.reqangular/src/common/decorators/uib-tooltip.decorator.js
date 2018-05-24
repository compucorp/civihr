define([
  'common/angular',
  'common/angularBootstrap'
], function () {
  'use strict';

  var $document, $element, $timeout, isTouchDevice;

  return ['$delegate', '$document', '$timeout', function ($delegate, _$document_, _$timeout_) {
    $document = _$document_;
    $timeout = _$timeout_;
    isTouchDevice = 'ontouchstart' in $document[0].documentElement;

    return function () {
      var tooltip = $delegate.apply(this, arguments);
      var compilator = tooltip.compile;

      tooltip.compile = function (tElement, tAttr) {
        return function link (scope, element, attributes, tooltipCtrl) {
          $element = element;

          compilator(tElement, tAttr)(scope, element, attributes, tooltipCtrl);

          if ($element.attr('uib-tooltip-clickable')) {
            makeTooltipClickable(attributes);
          }
        }
      }

      return tooltip;
    }
  }];

  function makeTooltipClickable (attributes) {
    const tooltip = {
      tooltip_hovered: false,
      day_hovered: false,
      open: false
    }

    var clickType = isTouchDevice ? 'touchstart' : 'click';

    function toggleTooltip (sourceElement, isHovered, event) {
      $timeout(function () {
        tooltip[sourceElement + '_hovered'] = isHovered;

        // this may be an alternative solution if .tooltip func does not work
        // but it does not work
        tooltip.open = tooltip.day_hovered || tooltip.tooltip_hovered;
        // THIS DOES NOT WORK
        $element.tooltip(tooltip.day_hovered || tooltip.tooltip_hovered ? 'show' : 'hide')
        /*document.getElementById('overlay').style.display =
          tooltip.day_hovered || tooltip.tooltip_hovered
          ? 'none'
          : 'block';*/
      }, isHovered ? 0 : 100);

      if (event) {
        event.stopPropagation();
      }
    }

    if (isTouchDevice) {
      /*document.getElementById('overlay').addEventListener(clickType, (event) => {
        toggleTooltip('day', !tooltip.day_hovered, event);
      });*/
      $document.find('body').on('touchstart', function (event) {
        toggleTooltip('day', false, event);
        toggleTooltip('tooltip', false, event);
      });
    } else {
      $element.on('mouseenter', function () {
        toggleTooltip('day', true);
      });

      $element.on('mouseleave', function () {
        toggleTooltip('day', false);
      });

      $element.find('.tooltip-template').on('mouseenter', function () {
        toggleTooltip('tooltip', true);
      });

      $element.find('.tooltip-template').on('mouseleave', function () {
        toggleTooltip('tooltip', false);
      });
    }

    attributes.tooltipTrigger = 'none';
    attributes.tooltipAnimation = false;
    // this may be an alternative solution if .tooltip func does not work
    // but it does not work
    attributes.tooltipIsOpen = tooltip.open;
  }
});
