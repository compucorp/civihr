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
      var contracts;
      var contactsLookUpStrategies = {
        all: loadAllContacts,
        me: loadMyManagees,
        unassigned: loadAllUnassignedContacts
      };

      return {
        loadContactsByAssignationType: loadContactsByAssignationType,
        loadFilteredContacts: loadFilteredContacts,
        loadLookUpContacts: loadLookUpContacts
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
       * Returns contacts depending on the selected assignation type (my assignees,
       * unnassigned contacts, or all contacts) and stores a list of lookup contact
       * ids to based on contacts with active contracts.
       *
       * @return {Promise} resolves to an array of contacts.
       */
      function loadContactsByAssignationType () {
        var filterByAssignee = _.get(vm, 'filters.userSettings.assignedTo.type', 'all');

        return loadLookUpContacts()
          .then(function (contacts) {
            vm.lookupContacts = contacts;

            return $q.all([
              loadFilteredContacts(),
              filterByAssignee !== 'me'
                ? loadContactIdsToReduceTo()
                : $q.resolve(null)
            ]);
          })
          .then(function (results) {
            var contacts = results[0];

            vm.contactIdsToReduceTo = results[1];

            return contacts;
          });
      }

      /**
       * Returns a promise of a list of contact ids for contacts with contracts
       * that are valid for the selected period's start and end dates.
       *
       * @return {Promise} resolves to an array of contact ids.
       */
      function loadContactIdsToReduceTo () {
        return loadContracts()
          .then(function (contracts) {
            var contractsInAbsencePeriod = contracts.filter(function (contract) {
              var details = contract.info.details;

              return (
                moment(details.period_start_date).isSameOrBefore(vm.selectedPeriod.end_date) &&
                (moment(details.period_end_date).isSameOrAfter(vm.selectedPeriod.start_date) ||
                  !details.period_end_date)
              );
            });

            return _.uniq(contractsInAbsencePeriod.map(function (contract) {
              return contract.contact_id;
            }));
          });
      }

      /**
       * Returns a list of all contracts. The result is cached locally.
       *
       * @return {Promise} resolves to an array of contracts.
       */
      function loadContracts () {
        return contracts ? $q.resolve(contracts) : Contract.all();
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

        if (hasContactFilter) {
          filters.id = { 'IN': [vm.filters.userSettings.contact.id] };
        } else if (hasLookUpContactsFilter) {
          filters.id = { 'IN': _.pluck(vm.lookupContacts, 'id') };
        }

        return filters;
      }
    }

    return {
      init: init
    };
  }
});
