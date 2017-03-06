define([
    'common/moment',
    'common/angularMocks',
    'job-contract/controllers/modal/modal-change-reason'
], function(moment) {
  'use strict';

  describe('ModalChangeReasonCtrl', function() {
    var $q, $rootScope, $scope, $controller, ctrl, modalInstanceSpy, ContractRevisionServiceMock, ContractRevisionServiceSpy;

    beforeEach(function(){
      module('hrjc.controllers')
      module(function($provide) {
        $provide.value('ContractRevisionService', ContractRevisionServiceMock);
      });

      ContractRevisionServiceMock = {
        validateEffectiveDate: function() {}
      }
    });

    beforeEach(inject(function(_$controller_, _$rootScope_, _$q_, ContractRevisionService) {
      $controller = _$controller_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      ContractRevisionServiceSpy = ContractRevisionService;

      modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['dismiss', 'close']);

      spyOn(window.CRM, 'alert');

      makeController();
    }));

    describe("when saving change reason form ", function() {
      it(" should have save() and cancel() fuctions defined", function() {
        expect($scope.save).toBeDefined();
        expect($scope.cancel).toBeDefined();
      });

      describe("if effective_date matches with available revisions ", function() {
        beforeEach(function() {
          spyOn(ContractRevisionServiceSpy, "validateEffectiveDate").and.callFake(function() {
            var deferred = $q.defer();
            deferred.resolve({
              success: false,
              message: 'Sample alert message'
            });

            return deferred.promise;
          });

          $scope.save();
          $scope.$digest();
        });

        it("should call ValidateEffectiveDate form ContractRevisionService to validate effective_date", function() {
          expect(ContractRevisionServiceSpy.validateEffectiveDate).toHaveBeenCalled();
        });

        it("should not close Modal", function() {
          expect(modalInstanceSpy.close).not.toHaveBeenCalled();
        });

        it("should call alert with message", function() {
          expect(window.CRM.alert).toHaveBeenCalled();
        });
      });

      describe("if effective_date does not match with available revisions ", function() {
        beforeEach(function() {
          spyOn(ContractRevisionServiceSpy, "validateEffectiveDate").and.callFake(function() {
            var deferred = $q.defer();
            deferred.resolve({
              success: true,
              message: ''
            });

            return deferred.promise;
          });

          $scope.save();
          $scope.$digest();
        });

        it("should close Modal ", function() {
          expect(modalInstanceSpy.close).toHaveBeenCalled();
        });

        it("should not call alert with message", function() {
          expect(window.CRM.alert).not.toHaveBeenCalled();
        });
      });
    });

    function makeController() {
      $scope = $rootScope.$new();

      $scope.copy = {};
      $scope.copy.title = 'Revision data';
      $scope.change_reason = '';
      $scope.effective_date = '';
      $scope.isPast = false;

      ctrl = $controller('ModalChangeReasonCtrl', {
        $scope: $rootScope,
        $uibModalInstance: modalInstanceSpy,
        content: "some string",
        date: "",
        reasonId: "",
        settings: "",
        ContractRevisionService: ContractRevisionServiceSpy
      });
    }
  });
});
