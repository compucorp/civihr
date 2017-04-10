define([
  'mocks/data/contact',
  'job-contract/app'
], function(ContactMock) {
  'use strict';

  describe('FormPensionCtrl', function() {
    var ctrl, $controller, $rootScope, $scope, $httpBackend, $q, ContactService, response, params;

    beforeEach(module('hrjc'));

    beforeEach(function() {
      inject(function(_$controller_, _$rootScope_, _$httpBackend_, _$q_, _ContactService_) {
        $controller = _$controller_;
        $rootScope = _$rootScope_;
        $httpBackend = _$httpBackend_;
        ContactService = _ContactService_;
        $q = _$q_;
        params = {
          contact_type: 'Organization',
          contact_sub_type: 'Life_Insurance_Provider'
        };
        contactServiceSpy();
      });
    });

    describe('On initialization', function() {
      describe('when pension_type is valid', function() {
        beforeEach(function() {
          initController(1);
          $scope.$digest();
        });

        it('defines contacts collection', function() {
          expect($scope.contacts).toBeDefined();
        });

        it("calls contact's service api to get one contact", function() {
          expect(ContactService.getOne).toHaveBeenCalled();
        });

        it("sets the values for contact's pension provider", function() {
          expect($scope.contacts.Pension_Provider.length).toBe(1);
          expect($scope.contacts.Pension_Provider[0].contact_id).toBe(ContactMock.contact.values[0].contact_id);
          expect($scope.contacts.Pension_Provider[0].contact_type).toBe(ContactMock.contact.values[0].contact_type);
        });
      });

      describe('when pension_type is not valid', function() {
        beforeEach(function() {
          initController();
          $scope.$digest();
        });

        it('defines contacts collection and pension_type', function() {
          expect($scope.contacts).toBeDefined();
          expect($scope.entity.pension.pension_type).toBe(null);
        });

        it("does not call contact's service api to get one contact", function() {
          expect(ContactService.getOne).not.toHaveBeenCalled();
        });

        it("does not set the values for contact's pension provider", function() {
          expect($scope.contacts.Pension_Provider.length).toBe(0);
        });
      });
    });

    describe('refreshContacts()', function() {
      beforeEach(function() {
        initController(1); // pension_type = 1
      });

      describe('when contact search text is not available', function() {
        beforeEach(function() {
          response = $scope.refreshContacts('', {});
        });

        it('returns response to be empty', function() {
          expect(response).toBeFalsy();
        });

        it('does not call search()', function() {
          expect(ContactService.search).not.toHaveBeenCalled();
        });
      });

      describe('when contact search text is available', function() {
        beforeEach(function () {
          $scope.refreshContacts('john', 'Life_Insurance_Provider');
          $scope.$digest();
        });

        it('calls contacts service api to search for contacts', function() {
          expect(ContactService.search).toHaveBeenCalled();
        });

        it('sets contact sub types data in contacts collection', function() {
          expect(ContactService.search).toHaveBeenCalled();
          expect($scope.contacts[params.contact_sub_type].length).toBe(1);
          expect($scope.contacts[params.contact_sub_type]).toEqual(ContactMock.contactSearchData.values);
        });
      });
    });

    /**
     * Creates FormPensionCtrl Controller
     * @param  integer pension_type
     * Note: Pension Type is set to null if no value is passed
     */
    function initController(pension_type) {
      var pension = {};

      pension.pension_type = pension_type || null;
      $scope = $rootScope.$new();
      $scope.entity = {};
      $scope.entity.pension = pension;

      ctrl = $controller('FormPensionCtrl', {
        $scope: $scope,
        ContactService: ContactService
      });
    }

    function contactServiceSpy() {
      spyOn(ContactService, "getOne").and.callFake(function() {
        return $q.resolve(ContactMock.contact.values[0]);
      });

      spyOn(ContactService, "search").and.callFake(function() {
        return $q.resolve(ContactMock.contactSearchData.values);
      });
    }
  });
});
