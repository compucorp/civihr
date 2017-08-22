/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarAdminController', ['$log', 'Contact', controller]);

  function controller ($log, Contact) {
    $log.debug('LeaveCalendarAdminController');

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
         * Returns all contacts
         * @return {Promise} resolves as an {Array}
         */
        loadContacts: function () {
          return Contact.all()
            .then(function (contacts) {
              vm.lookupContacts = contacts.list;
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
          return contacts.list;
        });
    }

    /**
     * Returns the filter object for contacts api
     *
     * @TODO This function should be a part of a Filter component, which is planned for future
     *
     * @return {Object}
     */
    function prepareContactFilters () {
      return {
        department: vm.filters.userSettings.department ? vm.filters.userSettings.department.value : null,
        level_type: vm.filters.userSettings.level_type ? vm.filters.userSettings.level_type.value : null,
        location: vm.filters.userSettings.location ? vm.filters.userSettings.location.value : null,
        region: vm.filters.userSettings.region ? vm.filters.userSettings.region.value : null,
        id: {
          'IN': vm.filters.userSettings.contact
            ? [vm.filters.userSettings.contact.id]
            : vm.lookupContacts.map(function (contact) {
              return contact.id;
            })
        }
      };
    }
  }
});
