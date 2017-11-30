/* eslint-env amd */

define(function () {
  'use strict';

  parseInt.__name = 'parseInt';
  parseInt.$inject = ['$log'];

  function parseInt ($log) {
    $log.debug('Filter: parseInt');

    return function (input) {
      return input ? parseInt(input) : null;
    };
  }

  return parseInt;
});
