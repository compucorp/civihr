/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/angular',
  'common/decorators/uib-tooltip.decorator',
  'common/lodash',
  'common/angularBootstrap'
], function (angularMocks, angular, $uibTooltipDecorator, _) {
  'use strict';

  describe('$uiTooltip.clickable', function () {
    var $compile, $content, $document, $rootScope, $triggerElement, $timeout, $tooltipElement, $provide;

    beforeEach(function () {
      module('ui.bootstrap');
      module(function (_$provide_, _$uibTooltipProvider_) {
        $provide = _$provide_;

        _$uibTooltipProvider_.setTriggers({ 'customShow': 'customHide' });
      });
      inject(function () {
        $provide.decorator('$uibTooltip', $uibTooltipDecorator);
      });
    });
    beforeEach(inject(function (_$compile_, _$document_, _$rootScope_, _$timeout_) {
      $compile = _$compile_;
      $document = _$document_;
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
    }));

    describe('when a document with a tooltip with the "uib-tooltip-clickable" attribute is opened', function () {
      beforeEach(function () {
        cleanDocument();

        $content = angular.element(
          '<div>' +
          generateTriggerElementHTML('tooltip1') +
          generateTriggerElementHTML('tooltip2') +
          '</div>');
        $triggerElement = $content.find('[uib-tooltip-template]').eq(0);
      });

      it('does not yet show the tooltip', function () {
        expect(getTooltipVisibility('tooltip1')).toBe(false);
      });

      describe('non-touch device', function () {
        beforeEach(function () {
          $compile($content)($rootScope.$new());
          $document.find('body').append($content);
        });

        describe('when the trigger element is hovered', function () {
          beforeEach(function () {
            $triggerElement.trigger('mouseenter');
            flushTimeout();

            $tooltipElement = $document.find('.tooltip-clickable-template');
          });

          it('shows the tooltip', function () {
            expect(getTooltipVisibility('tooltip1')).toBe(true);
          });

          describe('when the trigger tooltip is unhovered', function () {
            beforeEach(function () {
              $triggerElement.trigger('mouseleave');
              flushTimeout(2);
            });

            it('hides the tooltip', function () {
              expect(getTooltipVisibility('tooltip1')).toBe(false);
            });
          });

          describe('when the opened tooltip is hovered', function () {
            beforeEach(function () {
              $triggerElement.trigger('mouseleave');
              flushTimeout();
              $tooltipElement.trigger('mouseenter');
              flushTimeout();
            });

            it('keeps the tooltip shown', function () {
              expect(getTooltipVisibility('tooltip1')).toBe(true);
            });

            describe('when the opened tooltip is unhovered', function () {
              beforeEach(function () {
                $tooltipElement.trigger('mouseleave');
                flushTimeout(2);
              });

              it('keeps the tooltip shown', function () {
                expect(getTooltipVisibility('tooltip1')).toBe(false);
              });
            });
          });
        });
      });

      describe('touch device', function () {
        var $overlay;

        beforeEach(function () {
          simulateTouchDevice();

          $compile($content)($rootScope.$new());
          $document.find('body').append($content);

          $overlay = $triggerElement.find('.tooltip-overlay');
          $rootScope.$digest();
        });

        it('creates an properly styled overlay over the trigger element', function () {
          expect($overlay.length).toBe(1);
          expect($overlay[0].style.position).toBe('absolute');
          expect($overlay[0].style.height).toBe('100%');
          expect($overlay[0].style.height).toBe('100%');
          expect($overlay[0].style.width).toBe('100%');
          expect($overlay[0].style.top).toBe('0px');
          expect($overlay[0].style.left).toBe('0px');
          expect($overlay[0].style['z-index']).toBe('1');
        });

        describe('when the overlay is "hovered"', function () {
          beforeEach(function () {
            $overlay.trigger('mouseenter');
            flushTimeout();
          });

          it('ignores "hover" event and does not show the tooltip', function () {
            expect(getTooltipVisibility('tooltip1')).toBe(false);
          });
        });

        describe('when the overlay is tapped', function () {
          beforeEach(function () {
            $overlay.trigger('touchend');
            flushTimeout();

            $tooltipElement = $document.find('.tooltip-clickable-template');
          });

          it('shows the tooltip', function () {
            expect(getTooltipVisibility('tooltip1')).toBe(true);
          });

          it('hides the overlay', function () {
            expect(getOverlayVisibility($triggerElement)).toBe(false);
          });

          describe('when another trigger element is tapped', function () {
            var $triggerElement2, $overlay2;

            beforeEach(function () {
              $triggerElement2 = $content.find('[uib-tooltip-template]').eq(1);
              $overlay2 = $triggerElement2.find('.tooltip-overlay');

              $overlay2.trigger('touchend');
              flushTimeout();
            });

            it('opens the target tooltip and closes other tooltips', function () {
              expect(getTooltipVisibility('tooltip2')).toBe(true);
              expect(getTooltipVisibility('tooltip1')).toBe(false);
            });
          });

          describe('when the trigger element is tapped', function () {
            beforeEach(function () {
              $triggerElement.trigger('touchend');
              $rootScope.$digest();
              flushTimeout();
            });

            it('hides the tooltip', function () {
              expect(getTooltipVisibility('tooltip1')).toBe(false);
            });

            it('shows the overlay', function () {
              expect(getOverlayVisibility($triggerElement)).toBe(true);
            });
          });

          describe('when the tooltip is tapped', function () {
            beforeEach(function () {
              $tooltipElement.trigger('touchend');
              $rootScope.$digest();
              flushTimeout();
            });

            it('hides the tooltip', function () {
              expect(getTooltipVisibility('tooltip1')).toBe(false);
            });

            it('shows the overlay', function () {
              expect(getOverlayVisibility($triggerElement)).toBe(true);
            });
          });

          describe('when the trigger tooltip is unhovered', function () {
            beforeEach(function () {
              $document.find('body').trigger('touchend');
              $rootScope.$digest();
              flushTimeout();
            });

            it('hides the tooltip', function () {
              expect(getTooltipVisibility('tooltip1')).toBe(false);
            });
          });
        });
      });
    });

    /**
     * Cleans the document from tooltips trigger elements
     */
    function cleanDocument () {
      $document.find('[uib-tooltip-clickable]').remove();
    }

    /**
     * Flushes timeouts one or more times.
     * Multiple timeout flushes are needed because the tooltip library
     * has a timeout when hiding a tooltip.
     */
    function flushTimeout (times) {
      times = times || 1;

      _.times(times, function () {
        $timeout.flush();
      });
    }

    /**
     * Generates HTML for a trigger element with a given ID that is also used as the content
     *
     * @param  {String} id
     * @return {String}
     */
    function generateTriggerElementHTML (id) {
      return '<div uib-tooltip-template="\'' + id + '\'" uib-tooltip-clickable="true" tooltip-append-to-body="true">' +
        '<script id="' + id + '" type="text/ng-template">' +
        '<div class="tooltip-clickable-template">' + id + '</div>' +
        '</script>' +
        '</div>';
    }

    /**
     * Checks if the overlay of the given target element is currently visible
     *
     * @param  {jQuery} $triggerElement
     * @return {Boolean}
     */
    function getOverlayVisibility ($triggerElement) {
      return !!$triggerElement.find('.tooltip-overlay:visible').length;
    }

    /**
     * Checks if the tooltip is currently visible
     *
     * @return {Boolean}
     */
    function getTooltipVisibility (content) {
      return !!$document.find('.tooltip-clickable-template:visible:contains(' + content + ')').length;
    }

    /**
     * Simulates a touch device by simply telling the browser
     * that the `ontouchstart` event handler exists
     */
    function simulateTouchDevice () {
      $document[0].documentElement.ontouchstart = true;
    }
  });
});
