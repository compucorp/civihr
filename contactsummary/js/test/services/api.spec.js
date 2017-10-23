/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'contact-summary/app',
  'contact-summary/services/api'
], function () {
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
      var expectedResult = {data: true};
      var actualResult = {};

      // Set up response from HTTP backend
      httpBackend.expectGET('/civicrm/ajax/rest?action=get&entity=test&json=1&rowCount=0&sequential=1').respond(expectedResult);
      httpBackend.whenGET(function (url) {
        if (url !== '/civicrm/ajax/rest?action=get&entity=test&json=1&rowCount=0&sequential=1') {
          return true;
        }

        return false;
      }).respond(expectedResult);

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
