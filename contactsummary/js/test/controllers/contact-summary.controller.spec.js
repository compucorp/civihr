/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function () {
  'use strict';

  describe('ContactSummaryCtrl', function () {
    var $provide, ctrl, settingsMock;

    beforeEach(module('contactsummary', 'contactsummary.mocks',
    function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_settingsMock_) {
      settingsMock = _settingsMock_;

      $provide.constant('settings', settingsMock);
    }));

    beforeEach(inject(function ($controller) {
      ctrl = $controller('ContactSummaryController');
    }));

    it('stores the contact id', function () {
      expect(ctrl.contactId).toBe(settingsMock.contactId);
    });
  });
});
