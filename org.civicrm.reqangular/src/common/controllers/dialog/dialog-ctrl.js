/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/controllers'
], function (_, controllers) {
  'use strict';

  controllers.controller('DialogController', DialogController);

  DialogController.$inject = ['$q', '$scope', '$uibModalInstance', 'options'];

  function DialogController ($q, $scope, $modalInstance, options) {
    $scope.cancel = cancel;
    $scope.confirm = confirm;

    (function init () {
      assignOptionsToScope(options);

      if (options.optionsPromise) {
        options.optionsPromise()
          .then(function (_options_) {
            assignOptionsToScope(_options_);
          });
      }
    }());

    /**
     * Assigns passed options to the $scope,
     * uses default values for some of options
     *
     * @param {Object} options
     */
    function assignOptionsToScope (options) {
      _.assign($scope, _.defaultsDeep(options, {
        title: 'CiviHR',
        msg: '',
        copyConfirm: '',
        copyCancel: '',
        classConfirm: 'btn-primary',
        loading: false
      }));
    }

    /**
     * Handles cancellation action in the modal
     */
    function cancel () {
      $modalInstance.close(false);
    }

    /**
     * Handles confirmation action in the modal
     */
    function confirm () {
      $scope.loading = true;

      $modalInstance.closed.then(function () {
        $scope.onConfirm();
      });

      $modalInstance.close(true);
    }
  }
});
