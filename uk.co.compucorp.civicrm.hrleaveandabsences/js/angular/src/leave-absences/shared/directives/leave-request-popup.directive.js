/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/directives',
  'leave-absences/shared/controllers/sub-controllers/leave-request.controller',
  'leave-absences/shared/controllers/sub-controllers/sick-request.controller',
  'leave-absences/shared/controllers/sub-controllers/toil-request.controller'
], function (_, directives) {
  'use strict';

  directives.directive('leaveRequestPopup', ['$log', 'LeavePopupService',
    function ($log, LeavePopupService) {
      $log.debug('leaveRequestPopup');

      return {
        scope: {
          leaveRequest: '<',
          leaveType: '@',
          selectedContactId: '<',
          isSelfRecord: '<'
        },
        restrict: 'EA',
        link: function (scope, element) {
          element.on('click', function () {
            LeavePopupService.openModal(scope.leaveRequest, scope.leaveType, scope.selectedContactId, scope.isSelfRecord);
          });
        }
      };
    }
  ]);
});
