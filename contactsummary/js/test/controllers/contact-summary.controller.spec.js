/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/app'
], function () {
  'use strict';

  xdescribe('ContactSummaryCtrl', function () {
    var settingsMock;

    beforeEach(module('contactsummary', 'contactsummary.mocks'));

    beforeEach(module(function ($provide) {
      $provide.value('settings', function () {
        return settingsMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      // Instantiating injector isn't allowed before calls to 'module()'. Due to this limitation, we're returning
      // references to mock variables above, and injecting the actual mocks into them here, since they would be
      // lazily loaded anyway.
      settingsMock = $injector.get('settingsMock');
    }));
  });
});
