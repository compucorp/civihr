/* eslint-env amd */

define([
  'common/moment',
  'leave-absences/shared/modules/components'
], function (moment, components) {
  components.component('leaveRequestsHeatmap', {
    bindings: {
      leaveRequests: '<'
    },
    controller: LeaveRequestsHeatmapController,
    controllerAs: 'LeaveRequestsHeatmap',
    templateUrl: ['shared-settings', function (sharedSettings) {
      return sharedSettings.sharedPathTpl + 'components/leave-requests-heatmap.html';
    }]
  });

  function LeaveRequestsHeatmapController () {
    var vm = this;

    vm.$onChanges = $onChanges;

    /**
     * Implemenents the $onChanges method for Angular controllers.
     * When leaveRequests are bound, it maps them to heat map values.
     *
     * @param {OnChangesObject} changes - it has the previous and current value
     * for each bindings change. This value is passed by angular.
     */
    function $onChanges (changes) {
      if (changes.leaveRequests) {
        mapLeaveRequestsToHeatmapValues();
      }
    }

    /**
     * Stores the total leave balance for each day of the week.
     */
    function mapLeaveRequestsToHeatmapValues () {
      vm.heatmapValues = {};

      vm.leaveRequests.reduce(function (dates, request) {
        return dates.concat(request.dates);
      }, [])
      .forEach(function (date) {
        // 0 = Monday, 6 = Sunday:
        var dayOfTheWeek = moment(date.date).isoWeekday() - 1;

        if (!vm.heatmapValues[dayOfTheWeek]) {
          vm.heatmapValues[dayOfTheWeek] = 0;
        }

        vm.heatmapValues[dayOfTheWeek]++;
      });
    }
  }
});
