/* eslint-env amd */

define([
  'common/angular',
  'contact-summary/directives/donut-chart.directive'
], function (angular, csDonutChart) {
  'use strict';

  return angular.module('contactsummary.directives', [])
    .directive(csDonutChart.__name, csDonutChart);
});
