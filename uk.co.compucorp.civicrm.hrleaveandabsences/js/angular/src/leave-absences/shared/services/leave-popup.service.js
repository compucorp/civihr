/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/services',
  'common/services/notification.service'
], function (_, services) {
  'use strict';

  services.factory('LeavePopup', LeavePopupService);

  LeavePopupService.$inject = [
    '$log', '$rootElement', '$rootScope', '$q', '$uibModal', 'notificationService',
    'shared-settings', 'DateFormat', 'Session', 'LeaveRequest'
  ];

  function LeavePopupService ($log, $rootElement, $rootScope, $q, $modal, notification, sharedSettings, DateFormat, Session, LeaveRequest) {
    $log.debug('LeavePopup');

    return {
      openModal: openModal,
      openModalByID: openModalByID
    };

    /**
     * Checks if the current logged in contact can see the leave request
     *
     * @return {Promise}
     */
    function checkPermissionBeforeOpeningPopup (leaveRequest) {
      return Session.get()
        .then(function (sessionData) {
          return leaveRequest.roleOf(sessionData.contactId);
        })
        .then(function (role) {
          return role !== 'none';
        });
    }

    /**
     * Open leave request popup for the given leave request
     *
     * @param {LeaveRequestInstance} leaveRequest
     * @param {String} leaveType
     * @param {String} selectedContactId - Contact ID for the contact dropdown
     *                                     when the manager/admin is opening the request
     * @param {Boolean} isSelfRecord - True If the owner is opening the leave request
     */
    function openModal (leaveRequest, leaveType, selectedContactId, isSelfRecord) {
      $modal.open({
        appendTo: $rootElement.children().eq(0),
        templateUrl: sharedSettings.sharedPathTpl + 'directives/leave-request-popup/leave-request-popup.html',
        controller: 'RequestCtrl',
        controllerAs: '$ctrl',
        windowClass: 'chr_leave-request-modal',
        resolve: {
          directiveOptions: function () {
            return {
              leaveType: leaveType,
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
     * @return {Promise}
     */
    function openModalByID (leaveRequestID) {
      return LeaveRequest.find(leaveRequestID)
        .then(function (leaveRequest) {
          return checkPermissionBeforeOpeningPopup(leaveRequest)
            .then(function (hasPermission) {
              if (hasPermission) {
                openModal(leaveRequest, leaveRequest.request_type, leaveRequest.contact_id, $rootScope.section === 'my-leave');
              } else {
                notification.error('Error', 'You dont have permission to see this leave request');
              }
            });
        })
        .catch(function (errorMsg) {
          notification.error('Error', errorMsg);
        });
    }
  }
});
