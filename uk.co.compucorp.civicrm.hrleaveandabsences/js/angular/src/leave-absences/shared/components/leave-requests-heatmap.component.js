/* eslint-env amd */

define([
  'common/moment',
  'common/components/heatmap.component',
  'leave-absences/shared/modules/components'
], function (moment, heatmap, components) {
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

    vm.heatmapValues = {};

    vm.$onChanges = $onChanges;

    /**
     * Implemenents the $onChanges method for Angular controllers.
     * When leaveRequests are bound, it maps them to heat map values.
     *
     * @param {Object} changes - it has the previous and current value
     * for each bindings change. This value is passed by angular.
     */
    function $onChanges (changes) {
      if (changes.leaveRequests) {
        mapLeaveRequestsToHeatmapValues();
      }
    }

    /**
     * Stores the total leave balance for each day of the week. The heatmap
     * values are cleared to avoid displaying previous values.
     */
    function mapLeaveRequestsToHeatmapValues () {
      vm.heatmapValues = {};

      datesOfLeaveRequests().forEach(function (date) {
        var dayOfTheWeek = moment(date.date).isoWeekday();

        if (!vm.heatmapValues[dayOfTheWeek]) {
          vm.heatmapValues[dayOfTheWeek] = 0;
        }

        vm.heatmapValues[dayOfTheWeek]++;
      });
    }

    /**
     * Returns a single array of dates, extracted from each leave request
     * dates.
     *
     * @return {String[]}
     */
    function datesOfLeaveRequests () {
      return vm.leaveRequests.reduce(function (dates, request) {
        return dates.concat(request.dates);
      }, []);
    }
  }
});
