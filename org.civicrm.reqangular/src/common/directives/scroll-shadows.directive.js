/* eslint-env amd */
/* globals MutationObserver */

define([
  'common/angular',
  'common/modules/directives'
], function (angular, directives) {
  'use strict';

  directives.directive('scrollShadows', [function () {
    return {
      restrict: 'A',
      controller: scrollShadowsController
    };
  }]);

  scrollShadowsController.$inject = ['$element', '$scope', '$window'];

  function scrollShadowsController ($element, $scope, $window) {
    var $content, $shadows;
    var directiveClassPrefix = 'chr_scroll-shadows';

    (function init () {
      wrapContents();
      initToggleShadowsHandlers();
      toggleShadows();
    }());

    /**
     * Makes shadow toggle on scrolling, DOM changes, window resize and width changes
     */
    function initToggleShadowsHandlers () {
      toggleShadowsOnScroll();
      toggleShadowsOnDOMChange();
      toggleShadowsOnWindowResize();
      toggleShadowsOnWidthChange();
    }

    /**
     * Toggles shadows depending on the scroll position and element's width
     */
    function toggleShadows () {
      var contentWidth = $content.width();
      var leftShadowClassName = directiveClassPrefix + '__shadows--show-left';
      var rightShadowClassName = directiveClassPrefix + '__shadows--show-right';
      var scroll = $shadows.scrollLeft();
      var shadowsElementWidth = $shadows.width();

      if (contentWidth <= shadowsElementWidth) {
        $shadows.removeClass(leftShadowClassName + ' ' + rightShadowClassName);

        return;
      }

      $shadows[scroll > 0 ? 'addClass' : 'removeClass'](leftShadowClassName);
      $shadows[scroll < contentWidth - shadowsElementWidth ? 'addClass' : 'removeClass'](rightShadowClassName);
    }

    /**
     * Toggles shadows when element's DOM changes
     *
     * MutationObserver tracks DOM manipulation, for example,
     * when the content is being loaded.
     *
     * @NOTE When DOM is populated, the 'clientWidth' is not changed.
     */
    function toggleShadowsOnDOMChange () {
      (new MutationObserver(toggleShadows))
        .observe($content[0], { subtree: true, childList: true });
    }

    /**
     * Toggles shadows when element's width changes
     *
     * Tracking ClientWidth is needed to detect the width change via CSS or JavaScript.
     */
    function toggleShadowsOnWidthChange () {
      $scope.$watch(function () {
        return $content[0].clientWidth;
      }, toggleShadows);
      $scope.$watch(function () {
        return $shadows[0].clientWidth;
      }, toggleShadows);
    }

    /**
     * Toggles shadows when window is resized
     */
    function toggleShadowsOnWindowResize () {
      angular.element($window).on('resize', toggleShadows);
    }

    /**
     * Toggles shadows when element is scrolled
     */
    function toggleShadowsOnScroll () {
      $shadows.on('scroll', toggleShadows);
    }

    /**
     * Wraps original contents to make an environment for inset shadows
     */
    function wrapContents () {
      $content = $element.wrap('<div class="' + directiveClassPrefix + '__content"></div>').parent();
      $shadows = $content.wrap('<div class="' + directiveClassPrefix + '__shadows"></div>').parent();

      $shadows.wrap('<div class="' + directiveClassPrefix + '__wrapper"></div>');
    }
  }
});
