/* eslint-env amd */

define(function () {
  'use strict';

  getObjById.__name = 'getObjById';
  getObjById.$inject = ['$log'];

  function getObjById ($log) {
    $log.debug('Filter: getObjById');

    return function (input, id, key) {
      if (!input) {
        return null;
      }

      var i = 0;
      var len = input.length;

      for (; i < len; i++) {
        if (+input[i].id === +id) {
          return !key ? input[i] : input[i][key];
        }
      }
      return null;
    };
  }

  return getObjById;
});
