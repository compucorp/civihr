define([
  'common/angularMocks',
  'common/services/api/contact',
  'common/mocks/services/api/contact-mock'
], function () {
  'use strict';

  describe("api.contact", function () {
    var ContactAPI,
      $rootScope,
      ContactAPIMock,
      $q;

    beforeEach(module('common.apis', 'common.mocks'));

    beforeEach(inject(['api.contact', 'api.contact.mock', '$rootScope', '$q', function (_ContactAPI_, _ContactAPIMock_, _$rootScope_, _$q_) {
      ContactAPI = _ContactAPI_;
      ContactAPIMock = _ContactAPIMock_;
      $rootScope = _$rootScope_;
      $q = _$q_;
    }]));

    it("has expected interface", function () {
      expect(Object.keys(ContactAPI)).toContain("all");
      expect(Object.keys(ContactAPI)).toContain("find");
    });

    describe("all()", function () {
      var contactApiPromise,
        defer;

      beforeEach(function () {
        defer = $q.defer();
        spyOn(ContactAPI, 'getAll').and.returnValue(defer.promise);
      });

      beforeEach(function () {
        contactApiPromise = ContactAPI.all(jasmine.any(Object), jasmine.any(Object), jasmine.any(String), jasmine.any(Object));
        defer.resolve(ContactAPIMock.mockedContacts());
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it("returns all the contact", function () {
        contactApiPromise.then(function (result) {
          expect(result).toEqual(ContactAPIMock.mockedContacts());
        });
      });

      it("calls getAll method", function () {
        expect(ContactAPI.getAll).toHaveBeenCalledWith('Contact', jasmine.any(Object), jasmine.any(Object), jasmine.any(String), jasmine.any(Object));
      });
    });

    describe("find()", function () {
      var contactApiPromise,
        defer,
        mockID = '2';

      beforeEach(function () {
        defer = $q.defer();
        spyOn(ContactAPI, 'sendGET').and.returnValue(defer.promise);
      });

      beforeEach(function () {
        contactApiPromise = ContactAPI.find(mockID);
        defer.resolve({
          values: ContactAPIMock.mockedContacts().list
        });
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it("returns a contact", function () {
        contactApiPromise.then(function (result) {
          expect(result).toEqual(ContactAPIMock.mockedContacts().list[0]);
        });
      });

      it("calls sendGET method", function () {
        expect(ContactAPI.sendGET).toHaveBeenCalledWith('Contact', 'get', {id: '' + mockID}, false);
      });
    });
  });
});
