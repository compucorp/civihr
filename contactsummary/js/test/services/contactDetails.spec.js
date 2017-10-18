/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/moment',
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/app',
  'contact-summary/services/contactDetails'
], function (angular, moment) {
  'use strict';

  describe('ContactDetailsService', function () {
    var ApiServiceMock, ContactDetailsService, ModelServiceMock, rootScope;
    var settingsMock = {};

    beforeEach(module('contactsummary', 'contactsummary.mocks',
      'contact-summary.templates'));

    beforeEach(module(function ($provide) {
      $provide.factory('ApiService', function () {
        return ApiServiceMock;
      });

      $provide.factory('ModelService', function () {
        return ModelServiceMock;
      });

      $provide.constant('settings', settingsMock);
    }));

    beforeEach(inject(function ($injector) {
      ApiServiceMock = $injector.get('ApiServiceMock');
      ModelServiceMock = $injector.get('ModelServiceMock');
      rootScope = $injector.get('$rootScope');

      // We're extending because a reference to the original object was passed above, during bootstrap phase.
      angular.extend(settingsMock, $injector.get('settingsMock'));
    }));

    beforeEach(inject(function (_ContactDetailsService_) {
      ContactDetailsService = _ContactDetailsService_;
    }));

    describe('get', function () {
      var details;
      var expectedDateOfBirth = '1970/01/01';
      var expectedAge = moment().diff(moment(expectedDateOfBirth, 'YYYY-MM-DD'), 'years');
      var expectedResponse = {values: [{birth_date: expectedDateOfBirth}]};
      var expectedContactId = 123;

      beforeEach(function () {
        ApiServiceMock.respondGet('Contact', expectedResponse);
        settingsMock.contactId = expectedContactId;

        ContactDetailsService.get().then(function (response) {
          details = response;
        });

        rootScope.$digest();

        ApiServiceMock.flush();
      });

      it('should return contact details', function () {
        expect(details.id).toEqual(expectedContactId);
        expect(details.dateOfBirth).toEqual(expectedResponse.values[0].birth_date);
        expect(details.age).toEqual(expectedAge);
      });
    });
  });
});
