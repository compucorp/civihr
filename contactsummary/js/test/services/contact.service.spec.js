/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'mocks/services.mock',
  'contact-summary/modules/contact-summary.module',
  'contact-summary/services/contact.service'
], function () {
  'use strict';

  describe('contactService', function () {
    var $httpBackend, $q, $rootScope, contactService, contractService,
      contactDetailsServiceMock, modelServiceMock, expectedDetails, response;

    beforeEach(module('contactsummary', 'contactsummary.mocks',
      'contact-summary.templates'));

    beforeEach(module(function ($provide) {
      $provide.constant('settings', {
        CRM: { options: { 'HRJobDetails': { 'fieldName': 'fieldValues' } } }
      });

      $provide.factory('modelService', function () {
        return modelServiceMock;
      });

      $provide.factory('contactDetailsService', function () {
        return contactDetailsServiceMock;
      });
    }));

    beforeEach(inject(function ($injector) {
      modelServiceMock = $injector.get('modelServiceMock');
      contactDetailsServiceMock = $injector.get('contactDetailsServiceMock');
      $rootScope = $injector.get('$rootScope');
    }));

    beforeEach(inject(function (_$q_, _$httpBackend_, _contactService_, _contractService_) {
      $q = _$q_;
      $httpBackend = _$httpBackend_;
      contactService = _contactService_;
      contractService = _contractService_;
    }));

    beforeEach(function () {
      expectedDetails = { id: 123, dateOfBirth: '1970/01/01', age: 45 };
      response = [expectedDetails, expectedDetails];

      $httpBackend.whenGET(/entity=HRJobContract/).respond($q.resolve({ values: response }));
      contactDetailsServiceMock.respond('get', expectedDetails);
    });

    describe('get()', function () {
      var contact;

      beforeEach(function () {
        contactService.get().then(function (response) {
          contact = response;
        });

        $httpBackend.flush();
        $rootScope.$digest();
      });

      it('returns contact details', function () {
        expect(contact.id).toEqual(expectedDetails.id);
        expect(contact.dateOfBirth).toEqual(expectedDetails.dateOfBirth);
        expect(contact.age).toEqual(expectedDetails.age);
      });
    });

    describe('getOptions()', function () {
      var options;

      describe('when options are fetched without field name', function () {
        beforeEach(function () {
          contractService.getOptions().then(function (contractOptions) {
            options = contractOptions;
          });

          $rootScope.$digest();
        });

        it('returns the list of all contract options', function () {
          expect(options).toEqual({ details: { fieldName: 'fieldValues' } });
        });
      });

      describe('when options are fetched with field name', function () {
        beforeEach(function () {
          contractService.getOptions('fieldName').then(function (fieldOptions) {
            options = fieldOptions;
          });

          $rootScope.$digest();
        });

        it('returns the list of contract options for given field name only', function () {
          expect(options).toEqual({ details: 'fieldValues' });
        });
      });
    });
  });
});
