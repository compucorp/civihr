/* eslint-env amd */

define([
  'common/mocks/module'
], function (mocks) {
  'use strict';

  mocks.service('SessionMock', ['$q', function ($q) {
    var session = {
      contact_id: 999
    };

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
