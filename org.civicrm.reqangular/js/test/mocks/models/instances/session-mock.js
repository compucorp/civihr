/* eslint-env amd */

define([
  'common/mocks/module'
], function (mocks) {
  'use strict';

  mocks.service('SessionMock', ['$q', function ($q) {
    var session = {
      contactId: 999
    };

    /**
     * Returns user session
     *
     * @return {Promise} resolves with {Object}
     */
    function get () {
      return $q.resolve(session);
    }

    return {
      get: get,

      /**
       * The sesion object is returned for convenience when mocking the return
       * value of the promise.
       */
      sessionObject: session
    };
  }]);
});
