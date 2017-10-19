/* eslint-env amd */

define([
  'common/lodash',
  'common/modules/components'
], function (_, components) {
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
    var dayLabels = [
      { label: 'Monday', shortLabel: 'M' },
      { label: 'Tuesday', shortLabel: 'T' },
      { label: 'Wednesday', shortLabel: 'W' },
      { label: 'Thursday', shortLabel: 'T' },
      { label: 'Friday', shortLabel: 'F' },
      { label: 'Saturday', shortLabel: 'S' },
      { label: 'Sunday', shortLabel: 'S' }
    ];
    var vm = this;

    vm.days = [];

    vm.$onChanges = $onChanges;

    /**
     * Implementes the $onChanges method for angular controllers. When the
     * values binding is ready, it maps the values to the days and heat ranges.
     */
    function $onChanges () {
      if (vm.values) {
        mapValuesToDaysAndHeatRanges();
      }
    }

    /**
     * Transform a list of week day values into another list with their
     * heat range equivalent.
     *
     * The values are categorized into *low*, *medium*, *high*, and *disabled*
     * (in case the value is a strict *false*). For the *low* and *high* range
     * the minimum and maxim values are used. For the *medium* range the average
     * for all the values is selected.
     *
     * A value of *false* is treated as *disabled*.
     */
    function mapValuesToDaysAndHeatRanges () {
      var average, heatRanges, minimum;
      var arrayValues = _.toArray(vm.values).filter(function (value) {
        return value !== false;
      });

      average = Math.floor(_.sum(arrayValues) / arrayValues.length);
      // Minimum is the lowest value of the 7 days, or 0 if less than 7 days:
      minimum = arrayValues.length === 7 ? _.min(arrayValues) : 0;
      heatRanges = [
        { label: 'low', value: minimum },
        { label: 'medium', value: average },
        { label: 'high', value: _.max(arrayValues) }
      ];

      vm.days = dayLabels.map(function (dayLabel, day) {
        var heat;
        var value = vm.values[day];

        if (value === false) {
          return _.assign({ value: 0, heat: 'disabled' }, dayLabel);
        }

        value = value || 0;

        // returns the closest heat range:
        heat = heatRanges.reduce(function (previous, current) {
          return Math.abs(current.value - value) < Math.abs(previous.value - value)
            ? current
            : previous;
        });

        return _.assign({
          value: value,
          heat: heat.label
        }, dayLabel);
      });
    }
  }
});
