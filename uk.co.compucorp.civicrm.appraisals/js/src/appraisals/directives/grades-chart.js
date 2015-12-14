define([
    'common/d3',
    'appraisals/modules/directives'
], function (d3, directives) {
    'use strict';

    directives.directive('crmGradesChart', ['$log', function ($log) {
        var CHART_MARGINS = { top: 30, bottom: 30, left: 50, right: 30 };

        /**
         * Returns the inner and outer widths and heights of the chart area
         *
         * @param {object} element
         * @return {object}
         */
        function chartSize (element) {
            var size = {};

            size.outer = {
                height: element.clientHeight,
                width: element.clientWidth
            };

            size.inner = {
                width: size.outer.width - CHART_MARGINS.left - CHART_MARGINS.right,
                height: size.outer.height - CHART_MARGINS.top - CHART_MARGINS.bottom
            };

            return size;
        }

        /**
         * Checks that the mandatory params are passed to the directive
         *
         * @param {objects} attrs - The parameters passed to the directive
         * @return {boolean}
         */
        function checkMandatoryParams(attrs) {
            if (typeof attrs.chartData === 'undefined') {
                $log.error('[chart-data] is mandatory');

                return false;
            }

            return true;
        }


        /**
         * Resets the content of the directive element
         *
         * @param {object} element
         */
        function clearChart(element) {
            element.innerHTML = '';
        }

        /**
         * Returns the d3 axis objects
         *
         * @param {object} scaels - The d3 scales objects for the X and Y axis
         * @return {object}
         */
        function getAxis(scales) {
            return {
                x: d3.svg.axis()
                    .scale(scales.x)
                    .tickFormat(function (d, i) { return i + 1; })
                    .orient('bottom'),
                y: d3.svg.axis()
                    .scale(scales.y)
                    .orient('left')
                    .ticks(4)
            }
        }

        /**
         * Returns the d3 scale objects
         *
         * @param {object} size - The chart area sizes
         * @param {object} data - The chart data
         * @return {object}
         */
        function getScales(size, data) {
            return {
                x: d3.scale.ordinal()
                    .domain(d3.range(data.length))
                    .rangeBands([0, size.inner.width], .3),
                y: d3.scale.linear()
                    .domain([0, 100])
                    .range([size.inner.height, 0])
            }
        }

        /**
         *
         *
         */
        function drawChart(element, data) {
            var size = chartSize(element);
            var scales = getScales(size, data);
            var axis = getAxis(scales);

            var chart = d3.select(element)
                .append('svg')
                    .attr('width', size.outer.width)
                    .attr('height', size.outer.height)
                .append('g')
                    .attr('transform', 'translate(' + CHART_MARGINS.left + ', ' + CHART_MARGINS.top + ')');

            chart.append('g')
                .attr('class', 'chart-axis chart-axis-x')
                .attr('transform', 'translate(0,' + size.inner.height + ')')
                .call(axis.x);

            chart.append("g")
                .attr('class', 'chart-axis chart-axis-y')
                .call(axis.y);

            chart.selectAll('rect')
                .data(data).enter()
                .append('rect')
                    .attr('x', function (d, i) { return scales.x(i); })
                    .attr('y', function (d) { return scales.y(d.value); })
                    .attr('width', scales.x.rangeBand())
                    .attr('height', function (d) { return size.inner.height - scales.y(d.value); })
                    .attr('class', function (d, i) { return 'chart-color-' + i });
        }

        /**
         * Validates the params passed to the directive
         *
         * @return {object} chartData
         * @return {boolean}
         */
        function validateParams(chartData) {
            var valid = true;

            if (!Array.isArray(chartData)) {
                (valid = false) || $log.error('[chart-data] must be an array of objects');
            } else if (!chartData.every(function (entry) { return entry.label && entry.value })) {
                (valid = false) || $log.error('Elements in [chart-data] must have a `label` and a `value` property');
            }

            return valid;
        }

        return {
            scope: {
                chartData: '='
            },
            restrict: 'A',
            link: function (scope, element, attrs) {
                $log.debug('crmGradesChart');

                if (checkMandatoryParams(attrs)) {
                    element = element[0];

                    scope.$watch('chartData', function (chartData) {
                        clearChart(element);

                        if (validateParams(chartData)) {
                            drawChart(element, chartData);
                        }
                    });

                    window.onresize = function () {
                        clearChart(element);
                        drawChart(element, scope.chartData);
                    }
                }
            }
        };
    }]);
});
