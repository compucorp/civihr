define(['directives/directives', 'd3'], function (directives, d3) {
  'use strict';

  directives.directive('csDonutChart', ['$log', function ($log) {
    $log.debug('Directive: csDonutChart');

    var directive = {};

    directive.scope = {
      radius: '@',
      thickness: '@',
      items: '=',
      itemKey: '@',
      ready: '='
    };

    directive.restrict = 'AE';

    directive.link = function (scope, elem, attrs, ctrl) {
      var unbindWatch = scope.$watch(function () {
        return scope.ready;
      }, function (newValue, oldValue) {
        console.log(oldValue, newValue);
        if (newValue === true) {
          ctrl.drawChart();
          unbindWatch();
        }
      });
    };

    directive.controllerAs = 'CsDonutChartCtrl';

    directive.controller = ['$scope', '$element', function ($scope, $element) {
      this.drawChart = function () {
        init($scope);

        constructChart(
          constructSvg($element[0], $scope.width, $scope.height),
          constructArc($scope.radius - $scope.thickness, $scope.radius),
          constructData($scope.items, $scope.itemKey));
      };
    }];

    /////////////////////
    // Private Members //
    /////////////////////

    function init($scope) {
      $scope.radius = $scope.radius || 60;
      $scope.width = $scope.height = $scope.radius * 2;
      $scope.thickness = $scope.thickness || 15;
    }

    function constructArc(innerRadius, outerRadius) {
      return d3.svg.arc()
        .innerRadius(innerRadius)
        .outerRadius(outerRadius);
    }

    function constructSvg(element, width, height) {
      return d3.select(element).append('svg')
        .attr('width', width)
        .attr('height', height)
        .append('g')
        .attr('transform', 'translate(' + width / 2 + ',' + height / 2 + ')');
    }

    function constructData(items, key) {
      console.log('Key is', key);
      var pieLayout = d3.layout.pie()
        .sort(null)
        .value(function (d) {
          return d.value[key];
        });

      return pieLayout(d3.entries(items));
    }

    function constructChart(svg, arc, data) {
      var color = d3.scale.category20();

      return svg.selectAll('path')
        .data(data)
        .enter().append('path')
        .attr('fill', function (d, i) {
          return color(i);
        })
        .attr('d', arc);
    }

    return directive;
  }]);
});