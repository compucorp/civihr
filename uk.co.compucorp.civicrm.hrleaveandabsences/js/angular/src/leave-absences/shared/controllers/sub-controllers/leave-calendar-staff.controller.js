/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarStaffController', ['$log', 'Contact',
    'LeaveCalendarService', controller]);

  function controller ($log, Contact, LeaveCalendarService) {
    $log.debug('LeaveCalendarStaffController');

    var leaveCalendar, vm;

    return {
      /**
       * Initializes the sub-controller, passing the context (and thus the interface)
       * of the leave-calendar component's controller
       */
      init: function (_vm_) {
        vm = _vm_;
        leaveCalendar = LeaveCalendarService.init(vm);
        vm.filters.userSettings.contacts_with_leaves = true;
        vm.showTheseContacts = [vm.contactId];
        vm.showContactName = true;
        vm.showFilters = true;

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
         * Returns the data of the current contact.
         *
         * It displays a list of contacts taking leave for the current selected
         * period. If the display single contact property is set, it will only
         * fetch the information for the contact provided.
         *
         * @return {Promise} resolves as an {Array}
         */
        loadContacts: function () {
          if (vm.displaySingleContact) {
            vm.lookupContacts = [{ id: vm.contactId }];
          }

          if (!vm.displaySingleContact && vm.canManageRequests()) {
            return leaveCalendar.loadContactsByAssignationType();
          } else {
            return leaveCalendar.loadFilteredContacts();
          }
        }
      };
    }
  }
});
