/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/lodash',
  'common/components/heatmap.component'
], function (angular, _) {
  describe('Heatmap component', function () {
    var $componentController, cmp;

    beforeEach(function () {
      module('common.components');
      inject(function (_$componentController_) {
        $componentController = _$componentController_;
      });

      cmp = $componentController('heatmap');
    });

    it('must be defined', function () {
      expect(cmp).toBeDefined();
    });

    describe('on init', function () {
      it('sets days equal to an empty array', function () {
        expect(cmp.days).toEqual([]);
      });
    });

    describe('converting values to days and heat range', function () {
      var expectedDays;

      beforeEach(function () {
        var values = {
          0: 0,
          1: 2,
          2: 2,
          3: 10,
          4: 6,
          5: false,
          6: false
        };

        expectedDays = getExpectedDays([
          { value: 0, heat: 'low' },
          { value: 2, heat: 'low' },
          { value: 2, heat: 'low' },
          { value: 10, heat: 'high' },
          { value: 6, heat: 'medium' },
          { value: 0, heat: 'disabled' },
          { value: 0, heat: 'disabled' }
        ]);

        cmp.values = values;
        cmp.$onChanges({ values: { currentValue: values } });
      });

      it('converts the values to days and heat range', function () {
        expect(cmp.days).toEqual(expectedDays);
      });
    });

    describe('missing values', function () {
      var expectedDays;

      beforeEach(function () {
        var values = {
          2: 2,
          3: 9,
          4: 3
        };

        expectedDays = getExpectedDays([
          { value: 0, heat: 'low' },
          { value: 0, heat: 'low' },
          { value: 2, heat: 'low' },
          { value: 9, heat: 'high' },
          { value: 3, heat: 'medium' },
          { value: 0, heat: 'low' },
          { value: 0, heat: 'low' }
        ]);

        cmp.values = values;
        cmp.$onChanges({ values: { currentValue: values } });
      });

      it('sets the values to 0 and heat to low for missing days', function () {
        expect(cmp.days).toEqual(expectedDays);
      });
    });
  });

  function getExpectedDays (valuesAndHeats) {
    var days = [
      { shortLabel: 'M', label: 'Monday' },
      { shortLabel: 'T', label: 'Tuesday' },
      { shortLabel: 'W', label: 'Wednesday' },
      { shortLabel: 'T', label: 'Thursday' },
      { shortLabel: 'F', label: 'Friday' },
      { shortLabel: 'S', label: 'Saturday' },
      { shortLabel: 'S', label: 'Sunday' }
    ];

    return days.map(function (day, index) {
      return _.assign(day, valuesAndHeats[index]);
    });
  }
});
