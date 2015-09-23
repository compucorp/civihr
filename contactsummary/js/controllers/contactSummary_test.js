define(['angularMocks', 'app', 'controllers/contactSummary', 'services/keyDetails'], function () {
  'use strict';

  describe('ContactSummaryCtrl', function () {
    var ctrl, ctrlConstructor, log;

    beforeEach(module('contactsummary'));

    beforeEach(inject(function ($controller, $log) {
      ctrlConstructor = $controller;
      log = $log;
      ctrl = ctrlConstructor('ContactSummaryCtrl');
    }));

    it('should call "get" on KeyDetailsService', inject(function (KeyDetailsService) {
      var spyGet = spyOn(KeyDetailsService, 'get');
      ctrlConstructor('ContactSummaryCtrl', {$log: log, KeyDetailsService: KeyDetailsService});
      expect(spyGet).toHaveBeenCalled();
    }));

    it('should have key details', function () {
      expect(ctrl.keyDetails).toBeDefined();
    });
  });
});
