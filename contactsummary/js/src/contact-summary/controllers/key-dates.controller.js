/* eslint-env amd */

define([
  'common/angular',
  'common/moment'
], function (angular, moment) {
  'use strict';

  /**
   * Add the contract dates to the timeline
   *
   * @param {Object} contract
   */
  function addContractDates (contract) {
    this.dates.push({
      title: contract.title + ' (Start)',
      date: contract.start_date,
      future: isDateInFuture(contract.start_date)
    });

    if (contract.end_date) {
      this.dates.push({
        title: contract.title + ' (End)',
        date: contract.end_date,
        future: isDateInFuture(contract.end_date)
      });
    }
  }

  /**
   * Checks if a date is in the future
   *
   * @param {string} date
   * @return {boolean}
   */
  function isDateInFuture (date) {
    return moment().diff(date) < 0;
  }

  KeyDatesCtrl.__name = 'KeyDatesCtrl';
  KeyDatesCtrl.$inject = ['$log', 'ContractService', 'JobRoleService', 'pubSub'];

  function KeyDatesCtrl ($log, Contract, JobRole, pubSub) {
    $log.debug('Controller: KeyDatesCtrl');

    var self = this;
    this.ready = false;
    this.dates = [];
    this.activeContracts = 0;
    this.activeRoles = 0;

    /**
     * Fetch Contacts from Server
     * @ngdoc method
     * @name getContacts
     * @methodOf KeyDatesCtrl
     * @returns void
     */
    var getContacts = function () {
      Contract.get()
        .then(function (response) {
          angular.forEach(response, function (contract) {
            addContractDates.call(self, contract);

            if (contract.is_current === '1') {
              self.activeContracts++;
            }
          });

          return JobRole.get();
        })
        .then(function (response) {
          angular.forEach(response, function (role) {
            var endDate = moment(role.end_date);

            if (!endDate.isValid() || isDateInFuture(endDate)) {
              self.activeRoles++;
            }
          });
        })
        .finally(function () {
          self.ready = true;
        });
    };

    var resetKeyDates = function () {
      this.dates = [];
      getContacts();
    }.bind(this);

    getContacts();

    pubSub.subscribe('contract-refresh', resetKeyDates);
  }

  return KeyDatesCtrl;
});
