/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/services'
], function (_, moment, services) {
  'use strict';

  services.factory('LeaveCalendarService', LeaveCalendarService);

  LeaveCalendarService.$inject = [
    '$log', '$q', 'Contact', 'Contract'
  ];

  function LeaveCalendarService ($log, $q, Contact, Contract) {
    $log.debug('LeaveCalendarService');

    /**
     * Returns a list of common functions that are sahred between leave calendar
     * sub controller.
     *
     * @param {Object} vm - the sub controller's view model.
     * @return {Object} a collection of functions.
     */
    function init (vm) {
      var contactsLookUpStrategies = {
        all: loadAllContacts,
        me: loadMyManagees,
        unassigned: loadAllUnassignedContacts
      };

      return {
        loadContactsForAdmin: loadContactsForAdmin,
        loadFilteredContacts: loadFilteredContacts,
        loadLookUpContacts: loadLookUpContacts,
        loadLookUpAndFilteredContacts: loadLookUpAndFilteredContacts
      };

      /**
       * Returns a promise of all the contacts that can be used for look up
       * against the ids to reduce by.
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadAllContacts () {
        return Contact.all().then(function (contacts) {
          return contacts.list;
        });
      }

      /**
       * Returns a promise of all the contacts that are not assigned to another
       * contact.
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadAllUnassignedContacts () {
        return Contact.leaveManagees(undefined, {
          unassigned: true
        });
      }

      /**
       * Returns all contacts and stores a list of contacts to look up
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadContactsForAdmin () {
        return loadLookUpContacts()
          .then(function (contacts) {
            vm.lookupContacts = contacts;

            return loadFilteredContacts();
          });
      }

      /**
       * Returns a list of contacts reduced by the leave calendar filters and
       * sorts them by the contact's display name.
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadFilteredContacts () {
        return Contact.all(prepareContactFilters(), null, 'display_name')
          .then(function (contacts) {
            return contacts.list;
          });
      }

      /**
       * Loads a list of contacts that can be used for look up. The list is based
       * on the assignees type filter.
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadLookUpContacts () {
        var filterByAssignee = _.get(vm, 'filters.userSettings.assignedTo.type', 'all');
        var loadLookUpContactsMethod = contactsLookUpStrategies[filterByAssignee];

        return loadLookUpContactsMethod();
      }

      /**
       * Requests a list of look up contacts, stores them, and then returns
       * a list of filtered contacts based on the previously stored look ups.
       *
       * @return {Promise} resolve to an array of contacts.
       */
      function loadLookUpAndFilteredContacts () {
        return loadLookUpContacts()
          .then(function (lookupContacts) {
            vm.lookupContacts = lookupContacts;
          })
          .then(loadFilteredContacts);
      }

      /**
       * Returns a promise of all the contacts that are managed by the logged in
       * user.
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadMyManagees () {
        return Contact.leaveManagees(vm.contactId);
      }

      /**
       * Returns a map of filters to pass to the Contact API.
       *
       * @return {Object}
       */
      function prepareContactFilters () {
        var filters = {
          department: _.get(vm, 'filters.userSettings.department.value', null),
          level_type: _.get(vm, 'filters.userSettings.level_type.value', null),
          location: _.get(vm, 'filters.userSettings.location.value', null),
          region: _.get(vm, 'filters.userSettings.region.value', null)
        };
        var hasContactFilter = !!vm.filters.userSettings.contact;
        var hasLookUpContactsFilter = _.isArray(vm.lookupContacts) && vm.lookupContacts.length;
        var notRequestingAllContacts = _.get(vm, 'filters.userSettings.assignedTo.type', 'all') !== 'all';

        if (hasContactFilter) {
          filters.id = { 'IN': [vm.filters.userSettings.contact.id] };
        } else if (notRequestingAllContacts || hasLookUpContactsFilter) {
          filters.id = { 'IN': _.map(vm.lookupContacts, 'id') };
        }

        return filters;
      }
    }

    return {
      init: init
    };
  }
});
