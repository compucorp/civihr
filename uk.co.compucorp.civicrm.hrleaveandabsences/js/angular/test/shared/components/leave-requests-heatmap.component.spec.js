/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'mocks/helpers/controller-on-changes',
  'mocks/data/leave-request-data',
  'leave-absences/shared/components/leave-requests-heatmap.component'
], function (_, moment, controllerOnChanges, leaveRequestData) {
  describe('leaveRequestsHeatmap', function () {
    var $componentController, ctrl, leaveRequests;

    beforeEach(module('leave-absences.components'));

    beforeEach(inject(function (_$componentController_, _$rootScope_) {
      $componentController = _$componentController_;
      leaveRequests = leaveRequestData.all().values;
    }));

    beforeEach(function () {
      ctrl = $componentController('leaveRequestsHeatmap');
      controllerOnChanges.setupController(ctrl);
    });

    it('must be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('when leve requests are bound', function () {
      var expectedHeatMap;

      beforeEach(function () {
        controllerOnChanges.mockChange('leaveRequests', leaveRequests);

        expectedHeatMap = {};
        leaveRequests.reduce(function (dates, request) {
          return dates.concat(request.dates);
        }, [])
        .forEach(function (date) {
          var dayOfTheWeek = moment(date.date).isoWeekday();

          if (!expectedHeatMap[dayOfTheWeek]) {
            expectedHeatMap[dayOfTheWeek] = 0;
          }

          expectedHeatMap[dayOfTheWeek]++;
        });
      });

      it('transform leave requests into heatmap values', function () {
        expect(ctrl.heatmapValues).toEqual(expectedHeatMap);
      });
    });
  });
});
