/* eslint-env amd */

define([
  'leave-absences/shared/modules/services',
  'common/lodash',
  'common/services/notification'
], function (services, _) {
  'use strict';

  services.factory('LeavePopupService', [
    '$log', '$rootElement', '$rootScope', '$q', '$uibModal', 'checkPermissions', 'notification', 'shared-settings', 'DateFormat', 'Session', 'LeaveRequest',
    function ($log, $rootElement, $rootScope, $q, $modal, checkPermissions, notification, sharedSettings, DateFormat, Session, LeaveRequest) {
      $log.debug('LeavePopupService');

      return {
        openModal: openModal,
        openModalByID: openModalByID
      };

      /**
       * Checks if the current logged in contact can see the leave request
       *
       * @return {Boolean}
       */
      function checkPermissionBeforeOpeningPopup (leaveRequest) {
        var deferred = $q.defer();

        // check if admin
        checkPermissions(sharedSettings.permissions.admin.administer)
          .then(function (isAdmin) {
            if (isAdmin) {
              deferred.resolve(true);
            } else {
              // check if role is manager or owner
              return Session.get()
                .then(function (value) {
                  return leaveRequest.roleOf(value.contactId);
                })
                .then(function (role) {
                  deferred.resolve(role !== 'none');
                });
            }
          });

        return deferred.promise;
      }

      /**
       * Gets leave type.
       * If leaveTypeParam exits then its a new request, else if request
       * object exists then its edit request call
       *
       * @param {String} leaveTypeParam
       * @param {Object} request leave request for edit calls
       *
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

      /**
       * Open leave request popup for the given leave request
       *
       * @param {Object} leaveRequest
       * @param {String} leaveType
       * @param {String} selectedContactId
       * @param {Boolean} isSelfRecord
       */
      function openModal (leaveRequest, leaveType, selectedContactId, isSelfRecord) {
        var controller = _.capitalize(getLeaveType(leaveType, leaveRequest)) + 'RequestCtrl';

        $modal.open({
          appendTo: $rootElement.children().eq(0),
          templateUrl: sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup.html',
          // animation: scope.animationsEnabled,
          controller: controller,
          controllerAs: '$ctrl',
          windowClass: 'chr_leave-request-modal',
          resolve: {
            directiveOptions: function () {
              return {
                leaveRequest: leaveRequest,
                selectedContactId: selectedContactId,
                isSelfRecord: isSelfRecord
              };
            },
            // to set HR_settings DateFormat
            format: ['DateFormat', function (DateFormat) {
              // stores the data format in HR_setting.DATE_FORMAT
              return DateFormat.getDateFormat();
            }]
          }
        });
      }

      /**
       * Open leave request popup for a given ID
       *
       * @param {String} leaveRequestID
       */
      function openModalByID (leaveRequestID) {
        // check if the leave request exist
        LeaveRequest.find(leaveRequestID)
          .then(function (leaveRequest) {
            checkPermissionBeforeOpeningPopup(leaveRequest)
              .then(function (hasPermission) {
                hasPermission
                  ? openModal(leaveRequest, leaveRequest.request_type, leaveRequest.contact_id, $rootScope.section === 'my-leave')
                  : notification.alert('Error', 'You dont have permission to see this leave request');
              });
          })
          .catch(function (errorMsg) {
            notification.alert('Error', errorMsg);
          });
      }
    }]);
});
