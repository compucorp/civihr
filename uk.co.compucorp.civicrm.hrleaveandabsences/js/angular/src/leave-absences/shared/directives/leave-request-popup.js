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
        link: function (scope, element) {

          element.on('click', function (event) {
            $modal.open({
              templateUrl: settings.pathTpl + 'directives/leave-request-popup.html',
              animation: scope.animationsEnabled,
              controller: 'LeaveRequestPopupCtrl',
              controllerAs: '$ctrl',
              resolve: {
                contactId: function () {
                  return scope.contactId
                },
                //to set HR_settings DateFormat
                format: ['DateFormat', function (DateFormat) {
                  // stores the data format in HR_setting.DATE_FORMAT
                  return DateFormat.getDateFormat();
                }]
              }
            });
          });
        }
      };
    }
  ]);
});
