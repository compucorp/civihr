define(['angularMocks', 'controllers/contactSummary'], function (angular, angularMocks) {
  'use strict';

  describe('ContactSummaryCtrl', function () {
    var ctrl;

    beforeEach(module('controllers'));

    beforeEach(inject(function ($controller) {
      ctrl = $controller('ContactSummaryCtrl');
    }));

    it('should have key details', function () {
      expect(ctrl.keyDetails).toBeDefined();
    });
  });
});
