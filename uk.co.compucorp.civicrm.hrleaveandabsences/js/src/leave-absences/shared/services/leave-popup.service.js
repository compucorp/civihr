/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/angular-date',
  'leave-absences/shared/modules/services',
  'common/services/angular-date/date-format',
  'common/services/notification.service',
  'leave-absences/shared/controllers/request.controller'
], function (_, services) {
  'use strict';

  services.factory('LeavePopup', LeavePopupService);

  LeavePopupService.$inject = [
    '$log', '$rootElement', '$rootScope', '$uibModal', 'notificationService',
    'shared-settings', 'DateFormat', 'Session', 'LeaveRequest'
  ];

  function LeavePopupService ($log, $rootElement, $rootScope, $modal, notification, sharedSettings, DateFormat, Session, LeaveRequest) {
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
     * @param {Object} params
     * @param {LeaveRequestInstance} params.request - to edit a leave request
     * @param {String} params.leaveType - to open in the correct
     * @param {String} params.selectedContactId - to default to a specific contact
     * @param {Boolean} params.forceRecalculateBalanceChange
     * @param {String} params.defaultStatus - "approved", "rejected" etc.
     */
    function openModal (params) {
      $modal.open({
        appendTo: $rootElement.children().eq(0),
        templateUrl: sharedSettings.sharedPathTpl + 'components/leave-request-popup/leave-request-popup.html',
        controller: 'RequestCtrl',
        controllerAs: '$ctrl',
        windowClass: 'chr_leave-request-modal',
        resolve: {
          directiveOptions: function () {
            return params;
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
     * @param  {String} leaveRequestID
     * @return {Promise}
     */
    function openModalByID (leaveRequestID) {
      return LeaveRequest.find(leaveRequestID)
        .then(function (leaveRequest) {
          return checkPermissionBeforeOpeningPopup(leaveRequest)
            .then(function (hasPermission) {
              if (hasPermission) {
                openModal({
                  leaveRequest: leaveRequest,
                  leaveType: leaveRequest.request_type,
                  selectedContactId: leaveRequest.contact_id
                });
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
