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

    beforeEach(function() {
      $httpBackend.whenGET(/action=get&entity=HRJobContract/).respond({});
      $httpBackend.whenGET(/views.*/).respond({});
    });

    describe('On initialization', function() {
      describe('Case I: when $scope.entity.pension.pension_type is truthy', function() {
        beforeEach(function() {
          initController(1);
          $scope.$digest();
        });

        it('defines contact object', function() {
          expect($scope.contacts).toBeDefined();
        });

        it("calls getOne()", function() {
          expect(ContactService.getOne).toHaveBeenCalled();
        });

        it("sets value for $scope.contacts.Pension_Provider", function() {
          expect($scope.contacts.Pension_Provider.length).toBe(1);
          expect($scope.contacts.Pension_Provider[0].contact_id).toBe(ContactMock.contact.values[0].contact_id);
          expect($scope.contacts.Pension_Provider[0].contact_type).toBe(ContactMock.contact.values[0].contact_type);
        });
      });

      describe('Case II: when $scope.entity.pension.pension_type is falsy', function() {
        beforeEach(function() {
          initController(null);
          $scope.$digest();
        });

        it('defines contact object', function() {
          expect($scope.contacts).toBeDefined();
          expect($scope.entity.pension.pension_type).toBe(null);
        });

        it("calls getOne() $scope.entity.pension.pension_type is falsy", function() {
          expect(ContactService.getOne).not.toHaveBeenCalled();
        });

        it("does not set value for $scope.contacts.Pension_Provider", function() {
          expect($scope.contacts.Pension_Provider.length).toBe(0);
        });
      });
    });

    describe('refreshContacts()', function() {
      beforeEach(function() {
        initController(1);
      });

      describe('when input is falsy', function() {
        beforeEach(function() {
          response = $scope.refreshContacts('', {});
        });

        it('returns resoponse to be falsy', function() {
          expect(response).toBeFalsy();
        });

        it('does not call search()', function() {
          expect(ContactService.search).not.toHaveBeenCalled();
        });
      });

      describe('when input is truthy', function() {
        beforeEach(function () {
          $scope.refreshContacts('searchText', 'Life_Insurance_Provider');
          $scope.$digest();
        });

        it('calls search()', function() {
          expect(ContactService.search).toHaveBeenCalled();
        });

        it('sets contact_sub_types data in $scope.contacts', function() {
          expect(ContactService.search).toHaveBeenCalled();
          expect($scope.contacts[params.contact_sub_type].length).toBe(1);
          expect($scope.contacts[params.contact_sub_type]).toEqual(ContactMock.contactSearchData.values);
        });
      });
    });

    function initController(pension_type) {
      var pension = {};

      pension.pension_type = pension_type;
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
