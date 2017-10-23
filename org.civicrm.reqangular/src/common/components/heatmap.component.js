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
    var vm = this;

    vm.heatmapDays = [];

    vm.$onChanges = $onChanges;

    /**
     * Implementes the $onChanges method for angular controllers. When the
     * values binding is ready, it maps the values to the heatmap.
     */
    function $onChanges (changes) {
      if (changes.values) {
        mapValuesToHeatmapDays();
      }
    }

    /**
     * Returns a clean copy of the heatmap values that are not false.
     *
     * @return {Array}
     */
    function getCleanHeatValues () {
      return Object.values(vm.values).filter(function (value) {
        return value !== false;
      });
    }

    /**
     * Given a range of heat levels and a value, it will return the closest
     * heat level for the value.
     *
     * @param {Object[]} heatLevelRanges - An array of heat levels and their values.
     * @param {Number} heatValue - The value to compare against heat levels.
     *
     * @param {String}
     */
    function getHeatLevelForValue (heatLevelRanges, heatValue) {
      var heatRange = heatLevelRanges.reduce(function (previous, current) {
        return Math.abs(current.value - heatValue) < Math.abs(previous.value - heatValue)
          ? current
          : previous;
      });

      return heatRange.level;
    }

    /**
     * Returns the heat low, medium, and high ranges for the heatmap.
     * For the *low* and *high* range the minimum and maximum values are used.
     * For the *medium* range the average for all the values is selected.
     *
     * @return {Object[]}
     */
    function getHeatLevelRanges () {
      var average, minimum;
      var heatValues = getCleanHeatValues();

      average = Math.floor(_.sum(heatValues) / heatValues.length);
      // Minimum is the lowest value of the 7 days, or 0 if less than 7 days:
      minimum = heatValues.length < 7 ? 0 : _.min(heatValues);

      return [
        { level: 'low', value: minimum },
        { level: 'medium', value: average },
        { level: 'high', value: _.max(heatValues) }
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
        var dayLabel = moment().isoWeekday(dayNumber).format('dddd');

        return {
          dayLabel: dayLabel,
          shortDayLabel: dayLabel[0],
          heatLevel: 'low',
          heatValue: 0
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
      var heatLevelRanges = getHeatLevelRanges();

      resetHeatmapDays();

      _.forEach(vm.values, function (heatValue, isoWeekDayIndex) {
        var heatmapDay = vm.heatmapDays[isoWeekDayIndex - 1];

        if (heatValue === false) {
          heatmapDay.heatLevel = 'disabled';
          return;
        }

        heatmapDay.heatValue = heatValue;
        heatmapDay.heatLevel = getHeatLevelForValue(heatLevelRanges, heatValue);
      });
    }
  }
});
