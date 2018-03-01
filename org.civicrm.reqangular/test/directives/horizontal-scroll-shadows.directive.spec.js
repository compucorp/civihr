/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'common/directives/horizontal-scroll-shadows.directive'
], function (angular, _) {
  'use strict';

  describe('horizontalScrollShadows directive', function () {
    var $compile, $document, $rootScope, $window, element, scope;

    beforeEach(module('common.directives'));
    beforeEach(inject(function (_$compile_, _$document_, _$rootScope_, _$window_) {
      $compile = _$compile_;
      $document = _$document_;
      $rootScope = _$rootScope_;
      $window = _$window_;
    }));

    describe('when "horizontal-scroll-shadows" attribute is specified', function () {
      var content, wrapper;

      beforeEach(function () {
        scope = $rootScope.$new();
        element = angular.element('<div><div horizontal-scroll-shadows>Content</div></div>');

        $compile(element)(scope).controller('horizontalScrollShadows');

        $document.find('body').append(element);

        content = $document.find('.horizontal-scroll-shadows-content');
        wrapper = $document.find('.horizontal-scroll-shadows-wrapper');

        // Overflow is needed to be set to "auto", otherwise scroll won't work
        wrapper.css({ 'overflow-x': 'auto' });
      });

      describe('when not scrollable', function () {
        beforeEach(function () {
          wrapper.width(100);
          content.width(100);
        });

        it('does not add any shadows', function () {
          expect(wrapper.hasClass('insetShadowLeft')).toBe(false);
          expect(wrapper.hasClass('insetShadowRight')).toBe(false);
        });

        describe('but then window is resized so it becomes scrollable', function () {
          beforeEach(function () {
            wrapper.width(50);
            angular.element($window).trigger('resize');
          });

          it('adds a shadow to the right edge', function () {
            expect(wrapper.hasClass('insetShadowRight')).toBe(true);
          });
        });

        describe('but then content is changed so it becomes scrollable', function () {
          beforeEach(function () {
            content.width(800);
            content.append('<div style="width: 800px;">With long content</div>');
          });

          it('adds a shadow to the right edge', function () {
            expect(wrapper.hasClass('insetShadowRight')).toBe(true);
          });
        });

        describe('but then the width of the content is changed so it becomes scrollable', function () {
          beforeEach(function () {
            content.width(800);
          });

          it('adds a shadow to the right edge', function () {
            expect(wrapper.hasClass('insetShadowRight')).toBe(true);
          });
        });

        describe('but then the width of the wrapper is changed so it becomes scrollable', function () {
          beforeEach(function () {
            wrapper.width(800);
          });

          it('adds a shadow to the right edge', function () {
            expect(wrapper.hasClass('insetShadowRight')).toBe(true);
          });
        });
      });

      describe('when scrollable', function () {
        beforeEach(function () {
          wrapper.width(100);
          content.width(300);
        });

        it('adds a shadow to the right edge by default', function () {
          expect(wrapper.hasClass('insetShadowRight')).toBe(true);
        });

        describe('and is scrolled to', function () {
          describe('the left edge', function () {
            beforeEach(function () {
              // We need to trigger the handler manually, otherwise the
              // process of scrolling won't be finished by the time of the test
              wrapper.scrollLeft(0).trigger('scroll');
            });

            it('removes the shadow from the left edge', function () {
              expect(wrapper.hasClass('insetShadowLeft')).toBe(false);
            });

            it('adds a shadow to the right edge', function () {
              expect(wrapper.hasClass('insetShadowRight')).toBe(true);
            });
          });

          describe('somewhere in between edges', function () {
            beforeEach(function () {
              wrapper.scrollLeft(100).trigger('scroll');
            });

            it('adds shadows to both edges', function () {
              expect(wrapper.hasClass('insetShadowLeft')).toBe(true);
              expect(wrapper.hasClass('insetShadowRight')).toBe(true);
            });
          });

          describe('the right edge', function () {
            beforeEach(function () {
              wrapper.scrollLeft(200).trigger('scroll');
            });

            it('adds a shadow to the left edge', function () {
              expect(wrapper.hasClass('insetShadowLeft')).toBe(true);
            });

            it('removes the shadow from the right edge', function () {
              expect(wrapper.hasClass('insetShadowRight')).toBe(false);
            });
          });
        });
      });
    });
  });
});
