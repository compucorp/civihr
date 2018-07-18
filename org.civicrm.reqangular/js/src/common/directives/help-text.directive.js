/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'common/modules/directives',
  'common/services/notification.service'
], function (_, moment, directives) {
  directives.directive('helpText', ['$templateCache', function ($templateCache) {
    return {
      restrict: 'E',
      scope: {
        title: '@?'
      },
      transclude: true,
      controller: helpTextController,
      controllerAs: 'helpText',
      template: $templateCache.get('help-text.html'),
      link: helpTextLink
    };
  }]);

  helpTextController.$inject = ['$scope', 'notificationService'];

  function helpTextController ($scope, notificationService) {
    var defaultTitle = 'Help';
    var vm = this;

    vm.displayHelpText = displayHelpText;

    /**
     * Displays the help text using the notification service.
     * If a title is not provided, it will use *Help* by default.
     * The help text is provided by the $scope using `getHelpText()`
     */
    function displayHelpText () {
      var helpText = $scope.getHelpText();

      notificationService.info($scope.title || defaultTitle, helpText);
    }
  }

  function helpTextLink ($scope, $element, $attrs) {
    $scope.getHelpText = getHelpText;

    /**
     * Returns the help text's HTML string.
     */
    function getHelpText () {
      return $element.find('.help-text').html();
    }
  }
});
