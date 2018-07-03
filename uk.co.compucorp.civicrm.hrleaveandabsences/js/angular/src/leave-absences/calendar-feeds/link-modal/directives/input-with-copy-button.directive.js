/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  InputWithCopyButton.__name = 'inputWithCopyButton';
  InputWithCopyButton.$inject = ['shared-settings'];

  return InputWithCopyButton;

  function InputWithCopyButton (sharedSettings) {
    var templateUrl = sharedSettings.sourcePath + 'calendar-feeds/link-modal/directives/input-with-copy-button.html';

    return {
      controller: _.noop,
      controllerAs: 'input',
      link: InputWithCopyButtonLink,
      restrict: 'E',
      scope: {},
      templateUrl: templateUrl,
      require: {
        model: '^ngModel'
      }
    };

    function InputWithCopyButtonLink ($scope, $element, $attr, $ctrl) {
      var vm = $scope.input;

      vm.model = $ctrl.model;

      vm.copy = copy;

      /**
       * Copies the content of the input to the user's clipboard.
       */
      function copy () {
        var input = $element.find('input');

        input.select();
        document.execCommand('copy');
        input.blur();
      }
    }
  }
});
