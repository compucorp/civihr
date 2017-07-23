/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components',
  'leave-absences/shared/controllers/calendar-ctrl',
  'common/models/contact'
], function (_, moment, components) {
  components.component('managerLeaveCalendar', {
    bindings: {
      contactId: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/manager-leave-calendar.html';
    }],
    controllerAs: 'calendar',
    controller: [
      '$controller', '$log', '$q', '$rootScope', 'shared-settings', 'checkPermissions',
      'Calendar', 'Contact', 'ContactInstance', 'OptionGroup', controller]
  });

  function controller ($controller, $log, $q, $rootScope, sharedSettings, checkPermissions, Calendar, Contact, ContactInstance, OptionGroup) {
    $log.debug('Component: manager-leave-calendar');

    var isAdmin = false;
    var vm = $controller('CalendarCtrl');
    vm.contactId = this.contactId;
    vm.managedContacts = [];
    vm.filters = {
      contact: null,
      department: null,
      level_type: null,
      location: null,
      region: null,
      contacts_with_leaves: false
    };

    vm._contacts = function () {
      if (isAdmin) {
        return Contact.all()
          .then(function (contacts) {
            vm.managedContacts = contacts.list;

            return contacts.list;
          });
      }

      return ContactInstance.init({ id: vm.contactId })
        .leaveManagees()
        .then(function (contacts) {
          vm.managedContacts = contacts;
        })
        .then(function () {
          return loadContacts();
        });
    };

    (function init () {
      checkPermissions(sharedSettings.permissions.admin.administer)
      .then(function (_isAdmin_) {
        isAdmin = _isAdmin_;

        vm._init(function () {
          return loadOptionValues();
        });
      });
    })();

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
     * Loads the OptionValues necessary for the controller
     *
     * @return {Promise}
     */
    function loadOptionValues () {
      return OptionGroup.valuesOf([
        'hrjc_region',
        'hrjc_location',
        'hrjc_level_type',
        'hrjc_department'
      ])
      .then(function (data) {
        vm.regions = data.hrjc_region;
        vm.locations = data.hrjc_location;
        vm.levelTypes = data.hrjc_level_type;
        vm.departments = data.hrjc_department;
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
            : vm.managedContacts.map(function (contact) {
              return contact.id;
            })
        }
      };
    }

    return vm;
  }
});
