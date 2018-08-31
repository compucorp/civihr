/* eslint-env amd */

define([
  'common/angular'
], function (angular) {
  'use strict';

  hrjcValidate.$inject = ['$log'];

  function hrjcValidate ($log) {
    $log.debug('Directive: hrjcValidate');

    return {
      restrict: 'A',
      require: '^form',
      scope: {
        isWarning: '=?hrjcValidateWarning'
      },
      link: function ($scope, el, attrs, formCtrl) {
        var inputEl = el[0].querySelector('[name]');
        var inputNgEl = angular.element(inputEl);
        var inputName = inputNgEl.attr('name');
        var iconEl = document.createElement('span');
        var iconNgEl = angular.element(iconEl);

        if (!inputName) {
          return;
        }

        el.addClass('has-feedback');
        iconNgEl.addClass('glyphicon form-control-feedback');
        inputNgEl.after(iconNgEl);

        function toggleSuccess (invalid, isWarning) {
          el.toggleClass('has-success', !invalid && !isWarning);
          iconNgEl.toggleClass('glyphicon-ok', !invalid && !isWarning);
        }

        function toggleWarning (invalid, isWarning) {
          el.toggleClass('has-warning', !invalid && isWarning);
          iconNgEl.toggleClass('glyphicon-warning-sign', !invalid && isWarning);
        }

        function toggleError (invalid) {
          el.toggleClass('has-error', invalid);
          iconNgEl.toggleClass('glyphicon-remove', invalid);
        }

        $scope.$watch(function () {
          return formCtrl[inputName] && formCtrl[inputName].$invalid;
        }, function (invalid) {
          if (formCtrl[inputName].$dirty) {
            toggleSuccess(invalid, $scope.isWarning);
            toggleError(invalid);
          }
        });

        if (typeof $scope.isWarning !== 'undefined') {
          $scope.$watch('isWarning', function (isWarning) {
            var invalid = formCtrl[inputName].$invalid;
            if (formCtrl[inputName].$dirty) {
              toggleSuccess(invalid, isWarning);
              toggleWarning(invalid, isWarning);
            }
          });
        }

        inputNgEl.bind('blur', function () {
          toggleError(formCtrl[inputName].$invalid);
        });
      }
    };
  }

  return { hrjcValidate: hrjcValidate };
});
