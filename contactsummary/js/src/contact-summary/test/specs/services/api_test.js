define(['angularMocks', 'jQuery', 'app', 'services/api'], function () {
  'use strict';

  describe('ApiService', function () {
    var service, httpBackend;

    beforeEach(module('contactsummary'));

    beforeEach(inject(function (ApiService, $httpBackend) {
      service = ApiService;
      httpBackend = $httpBackend;
    }));

    // make sure no expectations were missed in your tests.
    // (e.g. expectGET or expectPOST)
    afterEach(function () {
      httpBackend.verifyNoOutstandingExpectation();
      httpBackend.verifyNoOutstandingRequest();
    });

    it('should return the expected result for "get"', function () {
      // Expected dummy data for HTTP call
      var expectedResult = {data: true}, actualResult = {};

      // Set up response from HTTP backend
      httpBackend.expectPOST('/civicrm/ajax/rest').respond(expectedResult);

      // Make the call
      service.get('test').then(function (response) {
        actualResult = response;
      });

      // Flush the backend to "execute" the request to do the expectedGET assertion.
      httpBackend.flush();

      // check the actualResult.
      expect(actualResult).toEqual(expectedResult);
    });
  });
});
