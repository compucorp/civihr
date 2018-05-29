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
        vm.showContactDetailsLink = true;
        vm.showContactName = true;
        vm.showFilters = true;
        vm.filtersByAssignee = [
          { type: 'me', label: 'People I approve' },
          { type: 'unassigned', label: 'People without approver' },
          { type: 'all', label: 'All' }
        ];
        vm.filters.userSettings.assignedTo = vm.filtersByAssignee[0];

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
          var filterByAssignee = vm.filters.userSettings.assignedTo.type;

          return lookupContacts(filterByAssignee)
            .then(function (contacts) {
              vm.lookupContacts = contacts;

              return $q.all([
                leaveCalendar.loadFilteredContacts(),
                filterByAssignee !== 'me'
                  ? leaveCalendar.loadContactIdsToReduceTo()
                  : $q.resolve(null)
              ]);
            })
            .then(function (results) {
              var contacts = results[0];

              vm.contactIdsToReduceTo = results[1];

              return contacts;
            });
        }
      };
    }

    /**
     * Returns the loading contacts promise depending on the
     * filter by assignee chosen
     *
     * @param  {String} filterByAssignee (me|unassigned|all)
     * @return {Promise} resolved to a list of loaded contacts
     */
    function lookupContacts (filterByAssignee) {
      if (filterByAssignee === 'me') {
        return Contact.leaveManagees(vm.contactId);
      } else if (filterByAssignee === 'unassigned') {
        return Contact.leaveManagees(undefined, {
          unassigned: true
        });
      } else {
        return leaveCalendar.loadAllLookUpContacts();
      }
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
