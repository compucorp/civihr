/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  InputWithCopyButton.__name = 'inputWithCopyButton';
  InputWithCopyButton.$inject = ['$timeout', 'shared-settings'];

  return InputWithCopyButton;

  function InputWithCopyButton ($timeout, sharedSettings) {
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
      var $input, justCopiedTimeout;
      var vm = $scope.input;

      vm.model = $ctrl.model;
      vm.justCopied = false;

      vm.copy = copy;
      vm.selectInputText = selectInputText;

      (function init () {
        $input = $element.find('input');
      }());

      /**
       * Copies the content of the input to the user's clipboard
       * and shows that the input has just been copied
       */
      function copy () {
        copyInputToClipboard();
        showJustCopied();
      }

      /**
       * Deselects the text in the copy input
       */
      function deselectInputText () {
        $input[0].setSelectionRange(0, 0);
      }

      /**
       * Copies the content of the input to the user's clipboard
       */
      function copyInputToClipboard () {
        selectInputText();
        document.execCommand('copy');
        deselectInputText();
      }

      /**
       * Selects whole text in the copy input
       */
      function selectInputText () {
        $input[0].setSelectionRange(0, $input.val().length);
      }

      /**
       * Shows that the input has just been copied
       * and reverts the button view to the original state in 2 seconds
       */
      function showJustCopied () {
        vm.justCopied = true;

        justCopiedTimeout && $timeout.cancel(justCopiedTimeout);

        justCopiedTimeout = $timeout(function () {
          vm.justCopied = false;
        }, 2000);
      }
    }
  }
});
