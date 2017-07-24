/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarManagerController', ['$log', 'Contact',
    'ContactInstance', controller]);

  function controller ($log, Contact, ContactInstance) {
    $log.debug('LeaveCalendarManagerController');

    var vm;

    return {
      /**
       * Initializes the sub-controller, passing the context (and thus the interface)
       * of the leave-calendar component's controller
       */
      init: function (_vm_) {
        vm = _vm_;
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
            .then(loadContacts);
        }
      };
    }

    /**
     * Load all contacts with respect to filters
     *
     * @return {Promise}
     */
    function loadContacts () {
      return Contact.all(prepareContactFilters(), null, 'display_name')
        .then(function (contacts) {
          return !vm.filters.contacts_with_leaves
            ? contacts.list
            : contacts.list.filter(function (contact) {
              return (vm.leaveRequests[contact.id] && Object.keys(vm.leaveRequests[contact.id]).length > 0);
            });
        });
    }

    /**
     * Returns the filter object for contacts api
     *
     * @return {Object}
     */
    function prepareContactFilters () {
      return {
        department: vm.filters.department ? vm.filters.department.value : null,
        level_type: vm.filters.level_type ? vm.filters.level_type.value : null,
        location: vm.filters.location ? vm.filters.location.value : null,
        region: vm.filters.region ? vm.filters.region.value : null,
        id: {
          'IN': vm.filters.contact
            ? [vm.filters.contact.id]
            : vm.lookupContacts.map(function (contact) {
              return contact.id;
            })
        }
      };
    }
  }
});
