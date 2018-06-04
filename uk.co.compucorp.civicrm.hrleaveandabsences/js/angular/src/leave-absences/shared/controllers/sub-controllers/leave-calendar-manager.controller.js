/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarManagerController', ['$log', 'Contact',
    'ContactInstance', 'LeaveCalendarService', controller]);

  function controller ($log, Contact, ContactInstance, LeaveCalendarService) {
    $log.debug('LeaveCalendarManagerController');

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
         * Returns the list of (filtered) contacts that the current contact manages
         *
         * @return {Promise} resolves as an {Array}
         */
        loadContacts: function () {
          return ContactInstance.init({ id: vm.contactId })
            .leaveManagees()
            .then(function (contacts) {
              vm.lookupContacts = contacts;
            })
            .then(function () {
              return leaveCalendar.loadFilteredContacts();
            });
        }
      };
    }
  }
});
