/* eslint-env amd */

define(function () {
  'use strict';

  formatPeriod.__name = 'formatPeriod';
  formatPeriod.$inject = ['$filter', '$log'];

  function formatPeriod ($filter, $log) {
    $log.debug('Filter: formatPeriod');

    return function (period) {
      return period ? $filter('date')(period, 'yyyy/MM/dd') : 'Unspecified';
    };
  }

  return formatPeriod;
});
