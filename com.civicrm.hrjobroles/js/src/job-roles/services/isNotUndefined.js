define([
  'job-roles/services/services',
], function (module) {
  'use strict';

  module.factory('isNotUndefined', function() {
    return function(array) {
      try {
        if (array.constructor === Array) {
          return array.filter(function (value) {
            return (value !== 'undefined' && value !== undefined);
          });
        } else {
          return array;
        }
      } catch (e) {
        return array;
      }
    };
  });
});
