define([
  'common/lodash',
  'leave-absences/shared/modules/directives',
  'leave-absences/shared/controllers/sub-controllers/leave-request-ctrl',
  'leave-absences/shared/controllers/sub-controllers/sick-request-ctrl',
  'leave-absences/shared/controllers/sub-controllers/toil-request-ctrl',  
], function (_, directives) {
  'use strict';

  directives.directive('leaveRequestPopup', ['$log', '$uibModal', 'shared-settings', 'DateFormat',
    function ($log, $modal, settings, DateFormat) {
      $log.debug('leaveRequestPopup');

      /**
       * gets leave type
       *
       * @param {String} leaveTypeParam
       * @return {String} leave type
       */
      function getLeaveType(leaveTypeParam) {
        var leaveType = 'leave';

        if (leaveTypeParam && leaveTypeParam !== 'holiday / vacation') {
          leaveType = leaveTypeParam;
        }

        return leaveType;
      }

      return {
        scope: {
          contactId: '<',
          leaveRequest: '<',
          leaveType: '@'
        },
        restrict: 'EA',
        link: function (scope, element) {
          var controller = _.capitalize(getLeaveType(scope.leaveType)) + 'RequestCtrl';

          element.on('click', function (event) {
            $modal.open({
              templateUrl: settings.pathTpl + 'directives/leave-request-popup.html',
              animation: scope.animationsEnabled,
              controller: controller,
              controllerAs: '$ctrl',
              resolve: {
                directiveOptions: function () {
                  return {
                    contactId: scope.contactId,
                    leaveRequest: scope.leaveRequest,
                    leaveType: scope.leaveType
                  };
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
