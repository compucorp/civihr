/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/moment',
  'common/angularMocks',
  'mocks/constants.mock',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module'
], function (angular, moment) {
  'use strict';

  describe('contactDetailsService', function () {
    var apiServiceMock, contactDetailsService, modelServiceMock, rootScope;
    var settingsMock = {};

    beforeEach(module('contactsummary', 'contactsummary.mocks',
      'contact-summary.templates'));

    beforeEach(module(function ($provide) {
      $provide.factory('apiService', function () {
        return apiServiceMock;
      });

      $provide.factory('modelService', function () {
        return modelServiceMock;
      });

      $provide.constant('settings', settingsMock);
    }));

    beforeEach(inject(function ($injector) {
      apiServiceMock = $injector.get('apiServiceMock');
      modelServiceMock = $injector.get('modelServiceMock');
      rootScope = $injector.get('$rootScope');

      // We're extending because a reference to the original object was passed above, during bootstrap phase.
      angular.extend(settingsMock, $injector.get('settingsMock'));
    }));

    beforeEach(inject(function (_contactDetailsService_) {
      contactDetailsService = _contactDetailsService_;
    }));

    describe('get', function () {
      var details;
      var expectedDateOfBirth = '1970/01/01';
      var expectedAge = moment().diff(moment(expectedDateOfBirth, 'YYYY-MM-DD'), 'years');
      var expectedResponse = {values: [{birth_date: expectedDateOfBirth}]};
      var expectedContactId = 123;

      beforeEach(function () {
        apiServiceMock.respondGet('Contact', expectedResponse);
        settingsMock.contactId = expectedContactId;

        contactDetailsService.get().then(function (response) {
          details = response;
        });

        rootScope.$digest();

        apiServiceMock.flush();
      });

      it('should return contact details', function () {
        expect(details.id).toEqual(expectedContactId);
        expect(details.dateOfBirth).toEqual(expectedResponse.values[0].birth_date);
        expect(details.age).toEqual(expectedAge);
      });
    });
  });
});
