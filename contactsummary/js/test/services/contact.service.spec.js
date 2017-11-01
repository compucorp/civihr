/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/services.mock',
  'contact-summary/services/contact.service'
], function () {
  'use strict';

  describe('contactService', function () {
    var contactDetailsServiceMock, contactService, contractServiceMock,
      modelServiceMock, rootScope;

    beforeEach(module('contactsummary', 'contactsummary.mocks',
      'contact-summary.templates'));

    beforeEach(module(function ($provide) {
      $provide.factory('modelService', function () {
        return modelServiceMock;
      });

      $provide.factory('contactDetailsService', function () {
        return contactDetailsServiceMock;
      });

      $provide.factory('contractService', function () {
        return contractServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      modelServiceMock = $injector.get('modelServiceMock');
      contactDetailsServiceMock = $injector.get('contactDetailsServiceMock');
      contractServiceMock = $injector.get('contractServiceMock');
      rootScope = $injector.get('$rootScope');
    }));

    beforeEach(inject(function (_contactService_) {
      contactService = _contactService_;
    }));

    describe('get', function () {
      var contact;
      var expectedDetails = {id: 123, dateOfBirth: '1970/01/01', age: 45};

      beforeEach(function () {
        contactDetailsServiceMock.respond('get', expectedDetails);

        contactService.get().then(function (response) {
          contact = response;
        });

        rootScope.$digest();
      });

      it('should return contact details', function () {
        expect(contact.id).toEqual(expectedDetails.id);
        expect(contact.dateOfBirth).toEqual(expectedDetails.dateOfBirth);
        expect(contact.age).toEqual(expectedDetails.age);
      });

      it('should return contracts');

      it('should return leaves');
    });
  });
});
