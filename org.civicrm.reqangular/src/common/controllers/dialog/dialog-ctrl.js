/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/controllers'
], function (_, controllers) {
  'use strict';

  controllers.controller('DialogController', DialogController);

  DialogController.$inject = ['$q', '$scope', '$uibModalInstance', 'props'];

  function DialogController ($q, $scope, $modalInstance, props) {
    $scope.cancel = cancel;
    $scope.confirm = confirm;

    (function init () {
      assignProps(props);
      props.delayedProps && props.delayedProps().then(function (_props_) {
        assignProps(_props_);
      });
    }());

    /**
     * Assignes passed properties to the $scope,
     * uses default values for some of properties
     *
     * @param {Object} props
     */
    function assignProps (props) {
      _.assign($scope, _.defaultsDeep(props, {
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
