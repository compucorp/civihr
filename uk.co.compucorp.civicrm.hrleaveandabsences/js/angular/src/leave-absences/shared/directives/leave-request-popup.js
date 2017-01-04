define([
  'leave-absences/shared/modules/directives',
  'leave-absences/shared/controllers/leave-request-popup-controller',
], function (directives) {
  'use strict';

  directives.directive('leaveRequestPopup', ['$log', '$uibModal', 'shared-settings', 'DateFormat',
    function ($log, $modal, settings, DateFormat) {
      $log.debug('leaveRequestPopup');

      return {
        scope: {
          contactId: '<'
        },
        restrict: 'EA',
        link: function (scope, element, attrs, ctrl) {
          $log.debug('link');

          element.on('click', function (event) {
            //to set HR_settings DateFormat
            DateFormat.getDateFormat().then(function (result) {
              scope.openComponentModal = function () {
                $modal.open({
                  templateUrl: settings.pathTpl + 'directives/leave-request-popup.html',
                  animation: scope.animationsEnabled,
                  controller: 'LeaveRequestPopupCtrl',
                  controllerAs: '$ctrl',
                  resolve: {
                    baseData: function() {
                      return {contactId: scope.contactId};
                    }
                  }
                });
              };
              scope.openComponentModal();
            });
          });
        }
      };
    }
  ]);
});
