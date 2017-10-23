/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/lodash',
  'common/components/heatmap.component'
], function (angular, angularMocks, _) {
  describe('Heatmap component', function () {
    var $componentController, ctrl;

    beforeEach(module('common.components'));

    beforeEach(inject(function (_$componentController_) {
      $componentController = _$componentController_;
    }));

    beforeEach(function () {
      ctrl = $componentController('heatmap');
    });

    it('must be defined', function () {
      expect(ctrl).toBeDefined();
    });

    describe('on init', function () {
      it('sets heatmap days equal to an empty array', function () {
        expect(ctrl.heatmapDays).toEqual([]);
      });
    });

    describe('converting values to days and heat range', function () {
      var expectedDays;

      beforeEach(function () {
        var values = {
          1: 1,
          2: 3,
          3: 3,
          4: 20,
          5: 12,
          6: 3,
          7: 0
        };

        expectedDays = getExpectedHeatmap([
          { heatValue: 1, heatLevel: 'low' },
          { heatValue: 3, heatLevel: 'low' },
          { heatValue: 3, heatLevel: 'low' },
          { heatValue: 20, heatLevel: 'high' },
          { heatValue: 12, heatLevel: 'medium' },
          { heatValue: 3, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' }
        ]);

        ctrl.values = values;
        ctrl.$onChanges({ values: { currentValue: values } });
      });

      it('converts the values to days and heat range', function () {
        expect(ctrl.heatmapDays).toEqual(expectedDays);
      });
    });

    describe('missing values', function () {
      var expectedDays;

      beforeEach(function () {
        var values = {
          3: 2,
          4: 9,
          5: 3
        };

        expectedDays = getExpectedHeatmap([
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 2, heatLevel: 'low' },
          { heatValue: 9, heatLevel: 'high' },
          { heatValue: 3, heatLevel: 'medium' },
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' }
        ]);

        ctrl.values = values;
        ctrl.$onChanges({ values: { currentValue: values } });
      });

      it('sets the values to 0 and heat to low for missing days', function () {
        expect(ctrl.heatmapDays).toEqual(expectedDays);
      });
    });

    describe('disabled values', function () {
      var expectedDays;

      beforeEach(function () {
        var values = {
          6: false,
          7: false
        };

        expectedDays = getExpectedHeatmap([
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'low' },
          { heatValue: 0, heatLevel: 'disabled' },
          { heatValue: 0, heatLevel: 'disabled' }
        ]);

        ctrl.values = values;
        ctrl.$onChanges({ values: { currentValue: values } });
      });

      it('marks false day values as disabled', function () {
        expect(ctrl.heatmapDays).toEqual(expectedDays);
      });
    });
  });

  /**
   * Given heat levels and values, it will return the expected heat map array
   * of objects.
   *
   * @param {Array} heatLevelsAndValues - An array of heat level and values
   */
  function getExpectedHeatmap (heatLevelsAndValues) {
    var days = [
      { shortName: 'M', longName: 'Monday' },
      { shortName: 'T', longName: 'Tuesday' },
      { shortName: 'W', longName: 'Wednesday' },
      { shortName: 'T', longName: 'Thursday' },
      { shortName: 'F', longName: 'Friday' },
      { shortName: 'S', longName: 'Saturday' },
      { shortName: 'S', longName: 'Sunday' }
    ];

    return days.map(function (day, index) {
      return {
        name: {
          long: day.longName,
          short: day.shortName
        },
        heat: {
          level: heatLevelsAndValues[index].heatLevel,
          value: heatLevelsAndValues[index].heatValue
        }
      };
    });
  }
});
