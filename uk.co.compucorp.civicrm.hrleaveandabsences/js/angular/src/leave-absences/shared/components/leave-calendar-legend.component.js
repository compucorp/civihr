/* eslint-env amd */

define([
  'leave-absences/shared/modules/components'
], function (components) {
  components.component('leaveCalendarLegend', {
    bindings: {
      absenceTypes: '<'
    },
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-calendar-legend.html';
    }],
    controllerAs: 'legend',
    controller: ['$log', controller]
  });

  function controller ($log) {
    $log.debug('Component: leave-calendar-legend');

    var vm = this;

    vm.legendCollapsed = true;
    vm.otherBadges = [
      { label: 'AM', description: 'AM Only' },
      { label: 'PM', description: 'PM Only' },
      { label: 'HH:MM', description: 'Time', cssClassSuffix: 'hours' },
      { label: '', description: 'Requested', cssClassSuffix: 'requested' },
      { label: 'AT', description: 'Accrued TOIL' }
    ];

    vm.getAbsenceTypeStyle = getAbsenceTypeStyle;

    /**
     * Uses the absence type color to return border and background color styles
     *
     * @param  {AbsenceTypeInstance} absenceType
     * @return {Object}
     */
    function getAbsenceTypeStyle (absenceType) {
      return {
        backgroundColor: absenceType.color,
        borderColor: absenceType.color
      };
    }
  }
});
