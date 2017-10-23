/* eslint-env amd */

define([
  'common/lodash',
  'common/moment',
  'common/modules/components'
], function (_, moment, components) {
  return components.component('heatmap', {
    bindings: {
      values: '<'
    },
    controller: heatmapController,
    controllerAs: 'heatmap',
    template: ['$templateCache', function ($templateCache) {
      return $templateCache.get('components/heatmap.html');
    }]
  });

  function heatmapController () {
    var heatLevelRanges = [];
    var vm = this;

    vm.heatmapDays = [];

    vm.$onChanges = $onChanges;

    /**
     * Implementes the $onChanges method for angular controllers. When the
     * values binding is ready, it maps the values to the heatmap.
     */
    function $onChanges (changes) {
      if (changes.values) {
        resetHeatmapDays();
        calculateHeatLevelRanges();
        mapValuesToHeatmapDays();
      }
    }

    /**
     * Returns a copy of the heatmap values that are not false.
     *
     * @return {Array}
     */
    function filterOutFalseHeatValues () {
      return Object.values(vm.values).filter(function (value) {
        return value !== false;
      });
    }

    /**
     * Given a range of heat levels and a value, it will return the closest
     * heat level for the value given.
     *
     * @param {Array} heatLevelRanges - An array of heat levels and their values.
     * @param {Number} heatValue - The value to compare against heat levels.
     *
     * @param {String}
     */
    function calculateHeatLevelForValue (heatLevelRanges, heatValue) {
      var heatRange = heatLevelRanges.reduce(function (previous, current) {
        var distanceToCurrentValue = Math.abs(current.value - heatValue);
        var distanceToPreviousValue = Math.abs(previous.value - heatValue);

        return distanceToCurrentValue < distanceToPreviousValue
          ? current
          : previous;
      });

      return heatRange.level;
    }

    /**
     * Given an array of heat values, it returns the highest one.
     *
     * @param {Array} heatValues - An array of heat values.
     * @return {Number}
     */
    function getHighHeatRange (heatValues) {
      return _.max(heatValues);
    }

    /**
     * Given an array of heat values, it returns the lowest one. If less than
     * 7 days are provided, the minimum value is 0.
     *
     * @param {Array} heatValues - An array of heat values.
     * @return {Number}
     */
    function getLowHeatRange (heatValues) {
      return heatValues.length < 7 ? 0 : _.min(heatValues);
    }

    /**
     * Given an array of heat values, it returns the average between all values.
     *
     * @param {Array} heatValues - An array of heat values.
     * @return {Number}
     */
    function getMediumHeatRange (heatValues) {
      return Math.floor(_.sum(heatValues) / heatValues.length);
    }

    /**
     * Calculates the heat low, medium, and high ranges for the heatmap.
     */
    function calculateHeatLevelRanges () {
      var heatValues = filterOutFalseHeatValues();

      heatLevelRanges = [
        { level: 'low', value: getLowHeatRange(heatValues) },
        { level: 'medium', value: getMediumHeatRange(heatValues) },
        { level: 'high', value: getHighHeatRange(heatValues) }
      ];
    }

    /**
     * Resets the heatmap days to their default values. This is done for each
     * day of the week.
     * _.range(1, 8) returns an array with the numbers from 1 to 7.
     * .isoWeekday(1) will return Monday, 7 will return Sunday.
     */
    function resetHeatmapDays () {
      vm.heatmapDays = _.range(1, 8).map(function (dayNumber) {
        var dayName = moment().isoWeekday(dayNumber).format('dddd');

        return {
          name: {
            long: dayName,
            short: dayName[0]
          },
          heat: {
            level: 'low',
            value: 0
          }
        };
      });
    }

    /**
     * Maps a list of week day values into the days for the heatmap.
     *
     * The values are categorized into *low*, *medium*, *high*, and *disabled*.
     * A value of *false* is treated as *disabled*.
     */
    function mapValuesToHeatmapDays () {
      _.forEach(vm.values, function (heatValue, isoWeekDayIndex) {
        var heatmapDay = vm.heatmapDays[isoWeekDayIndex - 1];

        if (heatValue === false) {
          heatmapDay.heat.level = 'disabled';
        } else {
          heatmapDay.heat.value = heatValue;
          heatmapDay.heat.level = calculateHeatLevelForValue(heatLevelRanges, heatValue);
        }
      });
    }
  }
});
