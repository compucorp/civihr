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
      expect(Object.keys(ContactAPI)).toContain("leaveManagees");
    });

    describe("all()", function () {
      var contactApiPromise,
        filters = {key: "filters"},
        pagination = {key: "pagination"},
        sort = "sort",
        additionalParams = {key: "additionalParams"};

      beforeEach(function () {
        spyOn(ContactAPI, 'getAll').and.returnValue($q.resolve(ContactAPIMock.mockedContacts()));
        contactApiPromise = ContactAPI.all(filters, pagination, sort, additionalParams);
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
        expect(ContactAPI.getAll).toHaveBeenCalledWith('Contact', filters, pagination, sort, additionalParams);
      });
    });

    describe("find()", function () {
      var contactApiPromise,
        contactId = '2',
        contact;

      beforeEach(function () {
        contact = ContactAPIMock.mockedContacts().list[0];
        spyOn(ContactAPI, 'sendGET').and.returnValue($q.resolve({
          values: [contact]
        }));
        contactApiPromise = ContactAPI.find(contactId);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it("returns a contact", function () {
        contactApiPromise.then(function (result) {
          expect(result).toEqual(contact);
        });
      });

      it("calls sendGET method", function () {
        expect(ContactAPI.sendGET).toHaveBeenCalledWith('Contact', 'get', {id: '' + contactId}, false);
      });
    });

    describe("leaveManagees()", function () {
      var contactApiPromise;

      beforeEach(function () {
        spyOn(ContactAPI, 'sendGET').and.returnValue($q.resolve({
          values: ContactAPIMock.mockedContacts().list
        }));
        contactApiPromise = ContactAPI.leaveManagees();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it("returns the contacts", function () {
        contactApiPromise.then(function (result) {
          expect(result).toEqual(ContactAPIMock.mockedContacts().list);
        });
      });

      it("calls sendGET method", function () {
        expect(ContactAPI.sendGET).toHaveBeenCalledWith('Contact', 'getleavemanagees');
      });
    });
  });
});
