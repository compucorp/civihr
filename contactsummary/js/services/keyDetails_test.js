define(['angularMocks', 'services/keyDetails'], function () {
  'use strict';

  describe('KeyDetailsService', function () {
    var service;

    beforeEach(module('services'));

    beforeEach(inject(function (KeyDetailsService) {
      service = KeyDetailsService;
    }));

    it('should return key details', function () {
      var details = service.get();

      expect(details.age).toBeDefined();
      expect(details.dateOfBirth).toBeDefined();
      expect(details.lengthOfService).toBeDefined();
      expect(details.governmentId).toBeDefined();
      expect(details.contractType).toBeDefined();
      expect(details.grossAnnualPay).toBeDefined();
      expect(details.hours).toBeDefined();
    });
  });
});