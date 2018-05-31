/* eslint-env amd */

define([
  'common/lodash',
  'leave-absences/shared/modules/components'
], function (_, components) {
  components.component('leaveCalendarLegend', {
    bindings: {
      absenceTypes: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-calendar-legend.html';
    }],
    controllerAs: 'legend',
    controller: leaveCalendarLegendController
  });

  leaveCalendarLegendController.$inject = ['$log', '$rootScope'];

  function leaveCalendarLegendController ($log, $rootScope) {
    $log.debug('Component: leave-calendar-legend');

    var vm = this;

    vm.absenceTypesToFilterBy = [];
    vm.legendCollapsed = false;
    vm.nonWorkingDayTypes = [
      { label: 'Weekend', cssClassSuffix: 'weekend' },
      { label: 'Public Holiday', cssClassSuffix: 'public-holiday' },
      { label: 'Non Working Day', cssClassSuffix: 'non-working-day' }
    ];
    vm.otherBadges = [
      { label: 'AM', description: 'AM Only' },
      { label: 'PM', description: 'PM Only' },
      { label: 'HH:MM', description: 'Time', cssClassSuffix: 'hours' },
      { label: '', description: 'Requested', cssClassSuffix: 'requested' },
      { label: 'AT', description: 'Accrued TOIL' }
    ];

    vm.checkIfAbsenceTypeIsSelectedForFiltering = checkIfAbsenceTypeIsSelectedForFiltering;
    vm.getAbsenceTypeStyle = getAbsenceTypeStyle;
    vm.resetFilteringByAbsenceTypes = resetFilteringByAbsenceTypes;
    vm.toggleFilteringByAbsenceType = toggleFilteringByAbsenceType;

    (function init () {
      initWatchers();
    }());

    /**
     * Checks if absence type is selected for filtering
     *
     * @param  {String} absenceTypeId
     * @return {Boolean}
     */
    function checkIfAbsenceTypeIsSelectedForFiltering (absenceTypeId) {
      var isIncludedInTheAbsenceTypeFilters = _.includes(vm.absenceTypesToFilterBy, absenceTypeId);
      var noAbsenceTypesFiltersHaveBeenSelected = !vm.absenceTypesToFilterBy.length;

      return noAbsenceTypesFiltersHaveBeenSelected || isIncludedInTheAbsenceTypeFilters;
    }

    /**
     * Uses the absence type color to return background color style
     *
     * @param  {AbsenceTypeInstance} absenceType
     * @return {Object}
     */
    function getAbsenceTypeStyle (absenceType) {
      return {
        backgroundColor: absenceType.color
      };
    }

    /**
     * Watches the state of the absence types filter
     */
    function initWatchers () {
      $rootScope.$new().$watch(function () {
        return vm.absenceTypesToFilterBy;
      }, function (newValue, oldValue) {
        if (newValue !== oldValue) {
          $rootScope.$emit('LeaveCalendar::updateFiltersByAbsenceType',
            _.compact(vm.absenceTypesToFilterBy));
        }
      }, true);
    }

    /**
     * Resets filtering by absence types
     */
    function resetFilteringByAbsenceTypes () {
      vm.absenceTypesToFilterBy = [];
    }

    /**
     * Toggles filtering by a given absence type
     *
     * @param {String} absenceTypeId
     */
    function toggleFilteringByAbsenceType (absenceTypeId) {
      var hasAlreadyBeenSelected = _.includes(vm.absenceTypesToFilterBy, absenceTypeId);

      if (hasAlreadyBeenSelected) {
        _.remove(vm.absenceTypesToFilterBy, function (_absenceTypeId_) {
          return absenceTypeId === _absenceTypeId_;
        });
      } else {
        vm.absenceTypesToFilterBy.push(absenceTypeId);
      }
    }
  }
});
