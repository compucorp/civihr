/* eslint-env amd */
/* globals MutationObserver */

define([
  'common/angular',
  'common/modules/directives'
], function (angular, directives) {
  'use strict';

  directives.directive('horizontalScrollShadows', [function () {
    return {
      restrict: 'A',
      controller: horizontalScrollShadowsController
    };
  }]);

  horizontalScrollShadowsController.$inject = ['$element', '$scope', '$window'];

  function horizontalScrollShadowsController ($element, $scope, $window) {
    var content, wrapper;

    (function init () {
      wrapContents();
      initToggleShadowsHandlers();
      toggleShadows();
    }());

    /**
     * Makes shadow toggle on the wrapper element scrolling,
     * or DOM changes inside content element, or window resize
     */
    function initToggleShadowsHandlers () {
      wrapper.on('scroll', toggleShadows);
      angular.element($window).on('resize', toggleShadows);
      (new MutationObserver(toggleShadows)).observe(content[0], { subtree: true, childList: true });
      $scope.$watch(function () {
        return wrapper[0].clientWidth;
      }, toggleShadows);
      $scope.$watch(function () {
        return content[0].clientWidth;
      }, toggleShadows);
    }

    /**
     * Toggles shadows depending on the scroll position
     */
    function toggleShadows () {
      var scroll = wrapper.scrollLeft();
      var contentWidth = content.width();
      var wrapperWidth = wrapper.width();

      if (contentWidth <= wrapperWidth) {
        wrapper.removeClass('insetShadowLeft insetShadowRight');

        return;
      }

      wrapper[scroll > 0 ? 'addClass' : 'removeClass']('insetShadowLeft');
      wrapper[scroll < contentWidth - wrapperWidth ? 'addClass' : 'removeClass']('insetShadowRight');
    }

    /**
     * Wraps original contents to make an environment for inset shadows
     */
    function wrapContents () {
      $element.wrap(angular.element(
        '<div class="horizontal-scroll-shadows-content"></div>'));
      content = $element.parent();
      content.wrap(angular.element(
        '<div class="horizontal-scroll-shadows-wrapper"></div>'));
      wrapper = content.parent();
      wrapper.wrap(angular.element(
        '<div class="horizontal-scroll-shadows-master"></div>'));
    }
  }
});
