/* eslint-env amd */
/* globals MutationObserver */

define([
  'common/angular',
  'common/modules/directives'
], function (angular, directives) {
  'use strict';

  directives.directive('hScrollShadows', [function () {
    return {
      restrict: 'A',
      controller: hScrollShadowsController
    };
  }]);

  hScrollShadowsController.$inject = ['$element', '$scope', '$window'];

  function hScrollShadowsController ($element, $scope, $window) {
    var content, wrapper;
    var directiveClassPrefix = 'chr_h-scroll-shadows';

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
      var wrapperClassPrefix = directiveClassPrefix + '__wrapper';
      var wrapperWidth = wrapper.width();

      if (contentWidth <= wrapperWidth) {
        wrapper.removeClass(wrapperClassPrefix + '--inset-shadow-left');
        wrapper.removeClass(wrapperClassPrefix + '--inset-shadow-right');

        return;
      }

      wrapper[scroll > 0 ? 'addClass' : 'removeClass'](wrapperClassPrefix + '--inset-shadow-left');
      wrapper[scroll < contentWidth - wrapperWidth ? 'addClass' : 'removeClass'](wrapperClassPrefix + '--inset-shadow-right');
    }

    /**
     * Wraps original contents to make an environment for inset shadows
     */
    function wrapContents () {
      content = $element.wrap('<div class="' + directiveClassPrefix + '__content"></div>').parent();
      wrapper = content.wrap('<div class="' + directiveClassPrefix + '__wrapper"></div>').parent();

      wrapper.wrap('<div class="' + directiveClassPrefix + '__master"></div>');
    }
  }
});
