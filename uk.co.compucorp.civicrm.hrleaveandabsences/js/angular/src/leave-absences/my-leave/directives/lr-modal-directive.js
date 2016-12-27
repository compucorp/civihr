define([
  'leave-absences/my-leave/modules/directives'
], function (directives) {
  'use strict';

  directives.directive('lrModelDirective', ['$log', '$uibModal', 'settings',
    function ($log, $modal, settings) {
      $log.debug('lrModelDirective');

      var vmDirective = {},
        modelOptions;

      vmDirective.leaveRequest = {
        fromDate: new Date(),
        toDate: new Date(),
        showDatePickerFrom: false,
        showDatePickerTo: false,
        isChangeExpanded: false
      };

      modelOptions = {
        templateUrl: settings.pathTpl + 'components/my-leave-request.html',
        controllerAs: 'modal',
        resolve: {
          leaveRequest: function () {
            return vmDirective.leaveRequest;
          }
        }
      };

      return {
        controller: function () {
          var vm = this;
          vm.leaveRequest = vmDirective.leaveRequest;

          vm.closeModal = function () {
            vmDirective.modalInstance.close();
          };

          vm.showModal = function () {
            vmDirective.modalInstance = $modal.open(modelOptions);
          };

          vm.saveModal = function () {
            vmDirective.modalInstance.close();
          }

          return vm;
        },
        restrict: 'EA',
        link: function (scope, element, attrs, ctrl) {
          $log.debug('link');
          var $ctrl = ctrl;
          var directiveController = this.controller;

          element.on('click', function (event) {
            modelOptions.controller = directiveController;
            ctrl.showModal();
          });
        }
      };
    }
  ]);
});
