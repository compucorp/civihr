/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers',
  'common/models/contract',
  'common/services/notification.service'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarAdminController', ['$log', '$q',
    'Contact', 'ContactInstance', 'Contract', 'notificationService', controller]);

  function controller ($log, $q, Contact, ContactInstance, Contract, notification) {
    $log.debug('LeaveCalendarAdminController');

    var contracts, vm;

    return {
      /**
       * Initializes the sub-controller, passing the context (and thus the interface)
       * of the leave-calendar component's controller
       */
      init: function (_vm_) {
        vm = _vm_;
        vm.showContactName = true;
        vm.showAdminFilteringHint = showAdminFilteringHint;
        vm.showFilters = true;
        vm.filtersByAssignee = [
          { type: 'me', label: 'People I approve' },
          { type: 'unassigned', label: 'People without approver' },
          { type: 'all', label: 'All' }
        ];
        vm.filters.userSettings.assignedTo = vm.filtersByAssignee[0];

        return api();
      }
    };

    /**
     * Get contact IDs filtered according to contracts that belong
     * to the currently selected absence period
     *
     * @return {Promise}
     */
    function getContactIdsToReduceTo () {
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

        vm.contactIdsToReduceTo = _.uniq(contractsInAbsencePeriod.map(function (contract) {
          return contract.contact_id;
        }));
      });
    }

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
              vm.lookupContacts = filterByAssignee !== 'all' ? contacts : contacts.list;
            })
            .then(function () {
              vm.contactIdsToReduceTo = null;

              (filterByAssignee !== 'me') && getContactIdsToReduceTo();

              return loadContacts();
            })
            .then(function (contacts) {
              return contacts;
            });
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
     * Load all contracts or retrieve them from cache
     *
     * @return {Promise}
     */
    function loadContracts () {
      if (contracts) {
        return $q.resolve(contracts);
      }

      return Contract.all();
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
          unassigned: true // @TODO update this once it is supported in API!!
        });
      } else {
        return Contact.all();
      }
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
