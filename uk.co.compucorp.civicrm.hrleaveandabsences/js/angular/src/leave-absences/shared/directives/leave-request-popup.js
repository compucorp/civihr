/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/directives',
  'leave-absences/shared/controllers/sub-controllers/leave-request-ctrl',
  'leave-absences/shared/controllers/sub-controllers/sick-request-ctrl',
  'leave-absences/shared/controllers/sub-controllers/toil-request-ctrl'
], function (_, directives) {
  'use strict';

  directives.directive('leaveRequestPopup', ['$log', '$rootElement', '$uibModal', 'shared-settings', 'DateFormat',
    function ($log, $rootElement, $modal, settings, DateFormat) {
      $log.debug('leaveRequestPopup');

      /**
       * Gets leave type.
       * If leaveTypeParam exits then its a new request, else if request
       * object exists then its edit request call
       *
       * @param {String} leaveTypeParam
       * @param {Object} request leave request for edit calls
       * @return {String} leave type
       */
      function getLeaveType (leaveTypeParam, request) {
        // reset for edit calls
        if (request) {
          return request.request_type;
        } else if (leaveTypeParam) {
          return leaveTypeParam;
        }
      }

      return {
        scope: {
          contactId: '<',
          leaveRequest: '<',
          leaveType: '@',
          selectedContactId: '<',
          isSelfRecord: '<'
        },
        restrict: 'EA',
        link: function (scope, element) {
          var controller = _.capitalize(getLeaveType(scope.leaveType, scope.leaveRequest)) + 'RequestCtrl';

          element.on('click', function (event) {
            $modal.open({
              appendTo: $rootElement.children().eq(0),
              templateUrl: settings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup.html',
              animation: scope.animationsEnabled,
              controller: controller,
              controllerAs: '$ctrl',
              windowClass: 'chr_leave-request-modal',
              resolve: {
                directiveOptions: function () {
                  return {
                    contactId: scope.contactId,
                    leaveRequest: scope.leaveRequest,
                    selectedContactId: scope.selectedContactId,
                    isSelfRecord: scope.isSelfRecord
                  };
                },
                // to set HR_settings DateFormat
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
