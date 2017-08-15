/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/shared/modules/components'
], function (_, moment, components) {
  components.component('leaveBalanceTabFilters', {
    controller: LeaveBalanceTabFiltersController,
    controllerAs: 'leaveBalanceTabFilters',
    bindings: {
      absencePeriods: '<',
      absenceTypes: '<',
      onFiltersChange: '&'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-balance-tab-filters.html';
    }]
  });

  function LeaveBalanceTabFiltersController () {
    var vm = this;

    vm.filters = { absencePeriod: null, absenceType: null };

    /**
     * Angular Hook that Watches over changes in the bindings for absence
     * periods and types, and selects the default values for the filter.
     * It also emits an a filters change event when the filters have value for
     * the first time.
     *
     * @param {Object} changes - The list of changes for current digest.
     */
    vm.$onChanges = function (changes) {
      if (changes.absencePeriods && vm.absencePeriods.length) {
        vm.filters.absencePeriod = getCurrentAbsencePeriod();
      }

      if (changes.absenceTypes && vm.absenceTypes.length) {
        vm.filters.absenceType = getFirstAbsenceTypeByTitle();
      }

      if (vm.filters.absencePeriod && vm.filters.absenceType) {
        emitOnFiltersChange();
      }
    };

    /**
     * This function is used on the view when the user clicks on *filter* button.
     */
    vm.filter = function () {
      emitOnFiltersChange();
    };

    /**
     * Emits the on filters change event, passing the filter values.
     */
    function emitOnFiltersChange () {
      vm.onFiltersChange({ $filters: vm.filters });
    }

    /**
     * Returns the current absence period. If there are none, it returns the
     * oldest one.
     *
     * @return {Object}
     */
    function getCurrentAbsencePeriod () {
      var currentAbsencePeriod = _.find(vm.absencePeriods, function (period) {
        return period.current;
      });

      if (!currentAbsencePeriod) {
        currentAbsencePeriod = vm.absencePeriods.reduce(function (a, b) {
          return moment(a.end_date).isAfter(b.end_date) ? a : b;
        });
      }

      return currentAbsencePeriod;
    }

    /**
     * Returns the first absence type sorted by title.
     *
     * @return {Object}
     */
    function getFirstAbsenceTypeByTitle () {
      return vm.absenceTypes.reduce(function (a, b) {
        return a.title.localeCompare(b.title) ? a : b;
      });
    }
  }
});
