define([
    'job-roles/filters/filters',
], function (module) {
    'use strict';

    module.filter('isNotUndefined', function() {
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
