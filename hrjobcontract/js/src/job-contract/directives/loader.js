/* eslint-env amd */

define([
  'job-contract/directives/directives'
], function (directives) {
  'use strict';

  directives.directive('hrjcLoader', ['$rootScope', '$log', function ($rootScope, $log) {
    $log.debug('Directive: hrjcLoader');

    return {
      link: function ($scope, element, attrs) {
        var loader = document.createElement('div');
        var isLoaderSet = false;
        var isPositionSet = false;

        loader.className = 'hrjc-loader spinner';

        (function init () {
          if (attrs.hrjcLoaderShow) {
            appendLoader();
          }

          initLIsteners();
        }());

        /**
        * Appends the loader in the element and
        * updates the element's position
        */
        function appendLoader () {
          if (!isPositioned()) {
            element.css('position', 'relative');
            isPositionSet = true;
          }

          element.append(loader);
          isLoaderSet = true;
        }

        /**
         * Checks if the element has position value set
         *
         * @return {Boolean}
         */
        function isPositioned () {
          var elementPosition = window.getComputedStyle(element[0]).position;

          return elementPosition === 'relative' || elementPosition === 'absolute' || elementPosition === 'fixed';
        }

        /**
         * Initializes listeners
         */
        function initLIsteners () {
          $scope.$on('hrjc-loader-show', function () {
            appendLoader();
          });
          $scope.$on('hrjc-loader-hide', function () {
            removeLoader();
          });
        }

        /**
         * Removes the loader from the element and
         * updates the element's postion
         */
        function removeLoader () {
          isLoaderSet && loader.parentNode.removeChild(loader);
          isLoaderSet = false;

          if (isPositionSet) {
            element.css('position', '');
          }
        }
      }
    };
  }]);
});
