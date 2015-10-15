define([
  'moment',
  'angularMocks',
  'app',
  'mocks/module',
  'mocks/constants',
  'mocks/services',
  'services/contactDetails'
], function (moment) {
  'use strict';

  describe('ContactDetailsService', function () {
    var ContactDetailsService,
      ApiServiceMock, ModelServiceMock, settingsMock = {},
      rootScope;

    beforeEach(module('contactsummary', 'contactsummaryMocks'));

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
      var details,
        expectedDateOfBirth = '1970/01/01',
        expectedAge = moment(moment(expectedDateOfBirth, 'YYYY-MM-DD')).fromNow(true),
        expectedResponse = {values: [{birth_date: expectedDateOfBirth}]}, expectedContactId = 123;

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

