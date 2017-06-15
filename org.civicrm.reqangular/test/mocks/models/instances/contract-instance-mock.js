/* eslint-env amd */

define([
  'common/lodash',
  'common/mocks/module'
], function (_, mocks) {
  'use strict';

  mocks.factory('ContractInstanceMock', ['$q', 'ContractInstance', function ($q, instance) {
    return _.assign(Object.create(instance), {

      /**
      * Checks if the given object is a modal instance
      *
      * @param {object} object
      * @return {boolean}
      */
      isInstance: function (object) {
        return _.isEqual(_.functions(object), _.functions(instance));
      }
    });
  }]);
});
