/* eslint-env amd */

define([
  'common/angular',
  'job-contract/filters/capitalize.filter',
  'job-contract/filters/format-amount.filter',
  'job-contract/filters/format-period.filter',
  'job-contract/filters/get-obj-by-id.filter',
  'job-contract/filters/parse-integer.filter'
], function (angular, capitalize, formatAmount, formatPeriod, getObjById, parseInteger) {
  'use strict';

  return angular.module('job-contract.filters', [])
    .filter(capitalize.__name, capitalize)
    .filter(formatAmount.__name, formatAmount)
    .filter(formatPeriod.__name, formatPeriod)
    .filter(getObjById.__name, getObjById)
    .filter(parseInteger.__name, parseInteger);
});
