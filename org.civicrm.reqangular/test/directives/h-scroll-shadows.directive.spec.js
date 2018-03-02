/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/angularMocks',
  'common/directives/h-scroll-shadows.directive'
], function (angular, _) {
  'use strict';

  describe('hScrollShadows', function () {
    var $compile, $document, $rootScope, $window, content, element, scope, wrapper;

    beforeEach(module('common.directives'));
    beforeEach(inject(function (_$compile_, _$document_, _$rootScope_, _$window_) {
      $compile = _$compile_;
      $document = _$document_;
      $rootScope = _$rootScope_;
      $window = _$window_;
    }));

    beforeEach(function () {
      scope = $rootScope.$new();
      element = angular.element('<div><div h-scroll-shadows>Content</div></div>');

      $compile(element)(scope).controller('hScrollShadows');

      $document.find('body').append(element);

      content = $document.find('.chr_h-scroll-shadows__content');
      wrapper = $document.find('.chr_h-scroll-shadows__wrapper');

      // Overflow is needed to be set to "auto", otherwise scroll won't work
      wrapper.css({ 'overflow-x': 'auto' });
    });

    describe('when content is not scrollable', function () {
      beforeEach(function () {
        wrapper.width(100);
        content.width(100);
      });

      it('does not add any shadows', function () {
        expect(wrapperHasShadow('left')).toBe(false);
        expect(wrapperHasShadow('right')).toBe(false);
      });

      describe('when the window is resized and the content becomes scrollable', function () {
        beforeEach(function () {
          wrapper.width(50);
          angular.element($window).trigger('resize');
        });

        it('adds a shadow to the right edge', function () {
          expect(wrapperHasShadow('right')).toBe(true);
        });

        describe('when the window is resized and the content becomes unscrollable', function () {
          beforeEach(function () {
            wrapper.width(2000);
            angular.element($window).trigger('resize');
          });

          it('remove the shadow from the right edge', function () {
            expect(wrapperHasShadow('right')).toBe(false);
          });
        });
      });

      describe('when the content is changed so it becomes scrollable', function () {
        beforeEach(function () {
          content.width(800);
          content.append('<div style="width: 800px;">With long content</div>');
          $rootScope.$digest();
        });

        it('adds a shadow to the right edge', function () {
          expect(wrapperHasShadow('right')).toBe(true);
        });
      });

      describe('when the width of the content is changed so it becomes scrollable', function () {
        beforeEach(function () {
          content.width(800);
          $rootScope.$digest();
        });

        it('adds a shadow to the right edge', function () {
          expect(wrapperHasShadow('right')).toBe(true);
        });
      });

      describe('when the width of the wrapper is changed so the content becomes scrollable', function () {
        beforeEach(function () {
          wrapper.width(50);
          $rootScope.$digest();
        });

        it('adds a shadow to the right edge', function () {
          expect(wrapperHasShadow('right')).toBe(true);
        });
      });
    });

    describe('when scrollable', function () {
      beforeEach(function () {
        wrapper.width(100);
        content.width(300);
      });

      it('adds a shadow to the right edge by default', function () {
        expect(wrapperHasShadow('right')).toBe(true);
      });

      describe('and is scrolled to', function () {
        describe('the left edge', function () {
          beforeEach(function () {
            // We need to trigger the handler manually, otherwise the
            // process of scrolling won't be finished by the time of the test
            wrapper.scrollLeft(0).trigger('scroll');
          });

          it('removes the shadow from the left edge', function () {
            expect(wrapperHasShadow('left')).toBe(false);
          });

          it('adds a shadow to the right edge', function () {
            expect(wrapperHasShadow('right')).toBe(true);
          });
        });

        describe('somewhere in between edges', function () {
          beforeEach(function () {
            wrapper.scrollLeft(100).trigger('scroll');
          });

          it('adds shadows to both edges', function () {
            expect(wrapperHasShadow('left')).toBe(true);
            expect(wrapperHasShadow('right')).toBe(true);
          });
        });

        describe('the right edge', function () {
          beforeEach(function () {
            wrapper.scrollLeft(200).trigger('scroll');
          });

          it('adds a shadow to the left edge', function () {
            expect(wrapperHasShadow('left')).toBe(true);
          });

          it('removes the shadow from the right edge', function () {
            expect(wrapperHasShadow('right')).toBe(false);
          });
        });
      });
    });

    /**
     * Checks if the wrapper has a shadow on the given side
     *
     * @param  {String} side left|right
     * @return {Boolean}
     */
    function wrapperHasShadow (side) {
      return wrapper.hasClass('chr_h-scroll-shadows__wrapper--inset-shadow-' + side);
    }
  });
});
