define(['angularMocks', 'app', 'services/keyDates'], function () {
  'use strict';

  describe('KeyDatesService', function () {
    var service;

    beforeEach(module('contactsummary'));

    beforeEach(inject(function (KeyDatesService) {
      service = KeyDatesService;
    }));

    it('should return key dates', function () {
      var dates = service.get();

      expect(dates.length).toBeGreaterThan(0);
    });
  });
});