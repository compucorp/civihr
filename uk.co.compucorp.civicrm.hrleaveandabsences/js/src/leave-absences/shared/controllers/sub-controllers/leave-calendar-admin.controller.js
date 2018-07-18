/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers',
  'common/models/contract',
  'common/services/notification.service'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarAdminController', ['$log', '$q',
    'Contact', 'ContactInstance', 'Contract', 'notificationService',
    'LeaveCalendarService', controller]);

  function controller ($log, $q, Contact, ContactInstance, Contract, notification,
    LeaveCalendarService) {
    $log.debug('LeaveCalendarAdminController');

    var leaveCalendar, vm;

    return {
      /**
       * Initializes the sub-controller, passing the context (and thus the interface)
       * of the leave-calendar component's controller
       */
      init: function (_vm_) {
        vm = _vm_;
        leaveCalendar = LeaveCalendarService.init(vm);
        vm.filters.userSettings.assignedTo = _.find(vm.filtersByAssignee, { type: 'me' });
        vm.showContactDetailsLink = true;
        vm.showContactName = true;
        vm.showFilters = true;

        vm.showAdminFilteringHint = showAdminFilteringHint;

        return api();
      }
    };

    /**
     * Returns the api of the sub-controller
     *
     * @return {Object}
     */
    function api () {
      return {
        /**
         * Returns all contacts
         *
         * @return {Promise} resolves as an {Array}
         */
        loadContacts: function () {
          return leaveCalendar.loadContactsForAdmin();
        }
      };
    }

    /**
     * Shows a hint to the filtering logic
     */
    function showAdminFilteringHint (comment) {
      notification.info('', [
        '<p>When <strong>All</strong> filter is selected, all staff members with contracts which are active in the selected absence period are displayed.</p>',
        '<p><strong>People I approve</strong> filter displays only staff members who you approve leave for.</p>',
        '<p><strong>People without approver</strong> filter displays all staff members with contracts which are active in the selected absence period and who do not have any leave approver assigned.</p>'
      ].join(''));
    }
  }
});
