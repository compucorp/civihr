/* eslint-env amd */

define(function () {
  'use strict';

  capitalize.$inject = ['$log'];

  function capitalize ($log) {
    $log.debug('Filter: capitalize');

    return function (input) {
      return (input) ? input.replace(/([^\W_]+[^\s-]*) */g, function (txt) { return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase(); }) : '';
    };
  }

  return { capitalize: capitalize };
});
