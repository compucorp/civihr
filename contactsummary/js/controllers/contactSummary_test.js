define(['angularMocks', 'app', 'controllers/contactSummary'], function () {
  'use strict';

  describe('ContactSummaryCtrl', function () {
    var ctrl;

    beforeEach(module('contactsummary'));

    beforeEach(inject(function ($controller) {
      ctrl = $controller('ContactSummaryCtrl');
    }));

    it('should have key details', function () {
      expect(ctrl.keyDetails).toBeDefined();
    });
  });
});
