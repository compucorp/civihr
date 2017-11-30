/* eslint-env amd */

define(function () {
  'use strict';

  formatAmount.__name = 'formatAmount';
  formatAmount.$inject = ['$log'];

  function formatAmount ($log) {
    $log.debug('Filter: formatAmount');

    return function (input) {
      return input && input.indexOf('.') === -1 ? input + '.00' : input;
    };
  }

  return formatAmount;
});
