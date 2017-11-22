/* eslint-env amd */

define([
  'common/angular',
  'common/d3'
], function (angular, d3) {
  'use strict';

  function init ($element) {
    this.height = this.width = $element[0].clientWidth;
    this.radius = this.width / 2 || 60;
    this.thickness = this.thickness || 15;
  }

  function constructArc () {
    return d3.svg.arc()
    .innerRadius(this.radius - this.thickness)
    .outerRadius(this.radius);
  }

  function constructChart (svg, arc, data) {
    var color = d3.scale.category20();

    return svg.selectAll('path')
    .data(data)
    .enter().append('path')
    .attr('fill', function (d, i) {
      return color(i);
    })
    .attr('class', function (d, i) {
      return 'chart-color-' + i;
    })
    .attr('d', arc);
  }

  function constructData () {
    var pieLayout = d3.layout.pie()
    .sort(null)
    .value(function (d) {
      return d.value[this.itemKey];
    }.bind(this));

    return pieLayout(d3.entries(this.items));
  }

  function constructSvg ($element) {
    return d3.select($element).append('svg')
    .attr('width', this.width)
    .attr('height', this.height)
    .append('g')
    .attr('transform', 'translate(' + this.width / 2 + ',' + this.height / 2 + ')');
  }

  csDonutChart.__name = 'csDonutChart';
  csDonutChart.$inject = ['$log'];

  function csDonutChart ($log) {
    $log.debug('Directive: csDonutChart');

    return {
      controllerAs: 'CsDonutChartCtrl',
      restrict: 'AE',
      scope: {
        radius: '@',
        thickness: '@',
        items: '=',
        itemKey: '@',
        ready: '='
      },
      controller: ['$scope', '$element', function ($scope, $element) {
        this.drawChart = function () {
          // angular.extend is necessary cause `bindToController` is
          // available only in angular > 1.3
          init.call(angular.extend(this, $scope), $element);

          constructChart(
            constructSvg.call(this, $element[0]),
            constructArc.call(this),
            constructData.call(this)
            );
        };
      }],
      link: function (scope, elem, attrs, ctrl) {
        var unbindWatch = scope.$watch(function () {
          return scope.ready;
        }, function (newValue, oldValue) {
          if (newValue === true) {
            ctrl.drawChart();
            unbindWatch();
          }
        });
      }
    };
  }

  return csDonutChart;
});
