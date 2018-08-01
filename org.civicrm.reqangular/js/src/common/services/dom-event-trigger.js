define([
  'common/modules/services'
], function (services) {
  'use strict';

  (function ($) {
    services.factory('DOMEventTrigger', function () {

      /**
       * It fires the given event (custom or standard) on the document
       * so that any non-angular script can catch it and do something with the
       * data passed with it
       *
       * @param  {string} event
       * @param  {any} data
       */
      return function (event, data) {
        $(document).trigger(event, data);
      }
    })
  }(CRM.$));
});
