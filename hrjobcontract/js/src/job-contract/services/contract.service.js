/* eslint-env amd */

define(function () {
  'use strict';

  Contract.$inject = ['$resource', 'settings', '$log'];

  function Contract ($resource, settings, $log) {
    $log.debug('Service: Contract');

    return $resource(settings.pathRest, {
      action: 'get',
      entity: 'HRJobContract',
      json: {}
    });
  }

  return { Contract: Contract };
});
