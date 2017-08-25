/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/controllers',
  'common/models/contract'
], function (_, moment, controllers) {
  controllers.controller('LeaveCalendarAdminController', ['$log', '$q',
    'Contact', 'Contract', controller]);

  function controller ($log, $q, Contact, Contract) {
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
            .then(function () {
              return $q.all([
                loadContacts(),
                getContactIdsToReduceTo()
              ]);
            })
            .then(function (results) {
              return results[0]; // return all contacts
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
          var isContractInAbsencePeriod = (
            (!details.period_start_date ||
            moment(details.period_start_date).diff(moment(vm.selectedPeriod.end_date)) <= 0) &&
            (!details.period_end_date ||
            moment(details.period_end_date).diff(moment(vm.selectedPeriod.start_date)) > 0)
          );

          return isContractInAbsencePeriod;
        });

        vm.contactIdsToReduceTo = _.uniq(contractsInAbsencePeriod.map(function (contract) {
          return contract.contact_id;
        }));
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
